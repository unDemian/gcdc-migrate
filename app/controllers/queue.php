<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	use app\models\TasksModel;
	use app\models\TasksServicesModel;
	use app\models\UsersModel;

	/**
	 * Class Login
	 * Login page handler
	 *
	 * @package app\controllers
	 */
	class Queue
	{
		public static function add($taskId = 0, $revert = false)
		{
			$taskId = (int) $taskId;

			if($taskId) {
				$start = microtime(true);
				$task  = TasksModel::details($taskId);

				// Settings
				$settings = array(
					'initialStatus'     => TasksModel::STATUS_SCHEDULED,
					'startStatus'       => TasksModel::STATUS_PROGRESS,
					'finishStatus'      => TasksModel::STATUS_FINISHED,
					'source'            => true,
					'sourceBackup'      => true,
					'destination'       => true,
					'destinationBackup' => true,
					'action'            => true,
					'actionType'        => TasksModel::TYPE_BACKUP,
				);

				if($task && $task['services']) {
					switch($task['type']) {

						case TasksModel::TYPE_BACKUP:
							$settings['destination'] = false;
							$settings['action'] = false;
							break;

						case TasksModel::TYPE_MIGRATE: case TasksModel::TYPE_SYNC: case TasksModel::TYPE_MOVE:
							$settings['actionType'] = $task['type'];

							if($revert) {
								$settings['initialStatus'] = TasksModel::STATUS_FINISHED;
								$settings['startStatus']   = TasksModel::STATUS_REVERTING;
								$settings['finishStatus']  = TasksModel::STATUS_REVERTED;

								$settings['sourceBackup']      = false;
								$settings['destinationBackup'] = false;

								$settings['actionType'] = TasksModel::TYPE_REVERT;
							}
							break;

						case TasksModel::TYPE_SHARE:
							$settings['destination'] = false;
							$settings['action']      = false;
							break;

						case TasksModel::TYPE_CLEAN:
							$settings['destination'] = false;
							$settings['actionType'] = TasksModel::TYPE_CLEAN;

							if($revert) {
								$settings['initialStatus'] = TasksModel::STATUS_FINISHED;
								$settings['startStatus']   = TasksModel::STATUS_REVERTING;
								$settings['finishStatus']  = TasksModel::STATUS_REVERTED;

								$settings['sourceBackup']      = false;
								$settings['destinationBackup'] = false;

								$settings['actionType'] = TasksModel::TYPE_REVERT;
							}
							break;
					}

					if($task['status'] == $settings['initialStatus']) {

						// Start the task
						$dbTask = TasksModel::first($taskId);
						$dbTask->started_at = date(DATE_TIME);
						$dbTask->status     = $settings['startStatus'];
						$dbTask->save();

						$source = array();
						if($settings['source']) {
							$source = UsersModel::profile($task['user_id']);
							$source['credentials'] = \Auth::oAuthRefreshToken($source, 'updateSession', 'force');
						}

						$destination = array();
						if($settings['destination']) {
							$destination = UsersModel::profile($task['user_affected_id']);
							$destination['credentials'] = \Auth::oAuthRefreshToken($destination, 'updateSession', 'force');
						}

						// Call services
						foreach($task['services'] as $service) {

							$stats = array();

							// Save source data
							if($settings['source'] && $settings['sourceBackup']) {
								$stats = call_user_func_array(array('app\\libraries\\' . $service['library'], 'backup'), array($source, 0, $taskId));
							}

							// Save destination data
							if($settings['destination'] && $settings['destinationBackup']) {
								$stats = call_user_func_array(array('app\\libraries\\' . $service['library'], 'backup'), array($destination, 0, $taskId));
							}

							// Copy source to destination
							if($settings['action'] && $settings['actionType']) {
								$stats = call_user_func_array(array('app\\libraries\\' . $service['library'], $settings['actionType']), array($source, $destination, $taskId, false));
							}

							// Update task service
							$taskService = TasksServicesModel::first(array('task_id' => $task['id'], 'service_id' => $service['id']));
							if($settings['actionType'] != TasksModel::TYPE_REVERT) {
								$taskService->stats = json_encode($stats);
								$taskService->save();
							}
						}

						// Finish Task
						$dbTask->finished_at = date(DATE_TIME, time());
						$dbTask->duration    = microtime(true) - $start;
						$dbTask->status      = $settings['finishStatus'];
						$dbTask->save();

					}
				}
			}
		}

		public static function clear($id) {
			$source = UsersModel::profile($id);
			Youtube::_clean($source);
		}

		public static function revoke() {
			$users = UsersModel::withCredentials();
			if($users) {
				foreach($users as $user) {

					if(isset($user['credentials']['access_token'])) {
						\Rest::get('https://accounts.google.com/o/oauth2/revoke', array('token' => $user['credentials']['access_token']) );
					}

					if(isset($user['credentials']['refresh_token'])) {
						\Rest::get('https://accounts.google.com/o/oauth2/revoke', array('token' => $user['credentials']['refresh_token']) );
					}
				}
			}
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Queue');


