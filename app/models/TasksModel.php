<?
	namespace app\models;

	use app\models\SharesModel;

	class TasksModel extends \Model
	{
		public static $schema = array(
			'table'  => 'tasks',
			'fields' => array( 'id', 'user_id', 'user_affected_id', 'group_id', 'type', 'title', 'contains', 'estimate', 'duration', 'viewed', 'started_at', 'finished_at', 'created_at', 'status' )
		);

		const TYPE_SYNC    = 'sync';
		const TYPE_MIGRATE = 'migrate';
		const TYPE_MOVE    = 'move';
		const TYPE_BACKUP  = 'backup';
		const TYPE_IMPORT  = 'import';
		const TYPE_REVERT  = 'revert';
		const TYPE_SHARE   = 'share';
		const TYPE_CLEAN   = 'clean';

		const STATUS_SCHEDULED  = 0;
		const STATUS_PROGRESS   = 1;
		const STATUS_FINISHED   = 2;
		const STATUS_REVERTING  = 3;
		const STATUS_REVERTED   = 4;

		/**
		 * Required in every model. Please do not edit!
		 *
		 * @param array $params
		 */
		public function __construct($params = array())
		{
			if($params) {
				parent::__construct($params);
			} elseif(isset($this->settings)) {
				unset($this->settings);
			}
		}

		public static function details($taskId)
		{
			$task = static::first($taskId)->toArray();

			$query = "
				SELECT
					TasksServices.*,
					Services.*
				FROM
					tasks_services TasksServices
					LEFT JOIN services Services
					       ON Services.id = TasksServices.service_id
				WHERE
					TasksServices.task_id = " . $taskId;

			$task['services']    = static::execute($query)->toArray();


			$query = "
					SELECT
						Shares.*
					FROM
						shares Shares
					WHERE
						Shares.task_id = " . $task['id'];
			$share = array();
			$share = SharesModel::execute($query)->toArray();
			if($share) {
				$share = current($share);
			}
			$task['share'] = $share;

			return $task;
		}

		public static function listingFor($conditions = array(), $limit = false)
		{
			$query = "
				SELECT
					Tasks.*,
					Source.email as source_email,
					SourceProfile.avatar as source_avatar,
					Destination.email as destination_email,
					DestinationProfile.avatar as destination_avatar
				FROM
					tasks Tasks
					LEFT JOIN users Source
					       ON Source.id = Tasks.user_id
					LEFT JOIN users_profiles SourceProfile
					 	   ON SourceProfile.user_id = Source.id
				    LEFT JOIN users Destination
					       ON Destination.id = Tasks.user_affected_id
					LEFT JOIN users_profiles DestinationProfile
					 	   ON DestinationProfile.user_id = Destination.id
				WHERE";

			if(isset($conditions['type'])) {
				if(is_array($conditions['type'])) {
					$query .= "(";
					foreach($conditions['type'] as $type) {
						$query .= " Tasks.type = '" . $type . "' OR ";
					}
					$query = substr($query, 0, -4) .  ") AND";

				} else {
					$query .= " Tasks.type = '" . $conditions['type'] . "' AND ";
				}

			}

			if(isset($conditions['status'])) {
				if(is_array($conditions['status'])) {
					$query .= "(";
					foreach($conditions['status'] as $stat) {
						$query .= " Tasks.status = '" . $stat . "' OR ";
					}
					$query = substr($query, 0, -4) .  ") AND";

				} else {
					$query .= " Tasks.status = '" . $conditions['status'] . "' AND ";
				}
			}

			if(isset($conditions['viewed'])) {
				$query .= " Tasks.viewed = '" . $conditions['viewed'] . "' AND ";
			}


			if(isset($conditions['group_id'])) {
				$query .= "( Tasks.group_id = '" . $conditions['group_id'] . "'";
			} else {
				$query .= "( Tasks.user_id = " . $conditions['user_id'];
				if(isset($conditions['user_affected_id'])) {
					$query .= " OR Tasks.user_affected_id = " . $conditions['user_affected_id'];
				}
			}

			$query .= ")
				ORDER BY Tasks.created_at DESC ";

			if($limit) {
				$query .= ' LIMIT ' . $limit . ' ';
			}
			$tasks = static::execute($query)->toArray();

			if($tasks) {
				foreach($tasks as $key => $task) {
					$query = "
					SELECT
						Services.name,
						Services.image_css
					FROM
						tasks_services TasksServices
						LEFT JOIN services Services
							   ON Services.id = TasksServices.service_id
					WHERE
						TasksServices.task_id = " . $task['id'];

					$tasks[$key]['services'] = static::execute($query)->toArray();

					$query = "
					SELECT
						Shares.*
					FROM
						shares Shares
					WHERE
						Shares.task_id = " . $task['id'];

					$share = array();
					$share = SharesModel::execute($query)->toArray();
					if($share) {
						$share = current($share);
					}
					$tasks[$key]['share'] = $share;
				}
			}

			return $tasks;
		}

		public static function markAsRead($params) {
			$query = 'UPDATE ' . static::$schema['table'] . ' SET viewed = "1" WHERE user_id = ' . $params['user_id'] . ' OR user_affected_id = ' . $params['user_affected_id'];
			static::execute($query);
		}
	}