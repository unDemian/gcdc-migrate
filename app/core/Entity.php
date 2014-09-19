<?
	/**
	 * Class Entity
	 */

	use app\models\TasksModel;
	use app\models\SharesModel;

	class Entity
	{
		public static function migrate($source, $destination, $syncTaskId = 0, $ignoreUpdate = false, $whitelist = array())
		{
			return static::_transfer($source, $destination, $syncTaskId, $ignoreUpdate, $whitelist);
		}

		public static function sync($source, $destination, $syncTaskId = 0, $ignoreUpdate = false)
		{
			$stats  = static::_transfer($source, $destination, $syncTaskId);
			$stats2 = static::_transfer($destination, $source, $syncTaskId);

			foreach($stats as $key => $stat) {
				$stats[$key] = $stat + $stats2[$key];
			}

			return $stats;
		}

		public static function move($source, $destination, $syncTaskId = 0, $ignoreUpdate = false)
		{
			$stats = static::_transfer($source, $destination, $syncTaskId);
			static::_clean($source, $source, $syncTaskId);
			return $stats;
		}

		public static function revert($source, $destination, $syncTaskId = 0, $ignoreUpdate = false)
		{
			$task = TasksModel::first($syncTaskId)->toArray();
			if($task) {
				$taskType = $task['type'];

				switch($taskType) {
					case TasksModel::TYPE_MIGRATE:
						static::_clean($destination, $source, $syncTaskId);
						static::_cleanDB($syncTaskId);
						break;

					case TasksModel::TYPE_SYNC:
						static::_clean($source, $destination, $syncTaskId);
						static::_clean($destination, $source, $syncTaskId);
						static::_cleanDB($syncTaskId);
						break;

					case TasksModel::TYPE_MOVE:
						static::_transfer($source, $source, $syncTaskId, 'ignoreUpdate');
						static::_clean($destination, $source, $syncTaskId);
						static::_cleanDB($syncTaskId);
						break;

					case TasksModel::TYPE_CLEAN:
						$share = SharesModel::first(array('task_id' => $syncTaskId));

						if($share) {
							$share = $share->toArray();
							$data  = json_decode($share['data'], true);
							static::_transfer($source, $source, $syncTaskId, 'ignoreUpdate', $data);
						}
						break;
				}
			}

			return array();
		}

		public static function _getColumn($array = array(), $column)
		{
			$final = array();
			if($array) {
				foreach($array as $item) {
					if(isset($item[$column])) {
						$final[$item[$column]] = $item;
					}
				}
			}

			return $final;
		}
	}