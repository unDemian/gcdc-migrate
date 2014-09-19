<?
	namespace app\models;

	use app\models\UsersServicesModel;
	use app\models\ServicesPermissionsModel;

	class ServicesModel extends \Model
	{
		public static $schema = array(
			'table'  => 'services',
			'fields' => array( 'id', 'name', 'image', 'image_css', 'library', 'link', 'scopes', 'quota', 'mandatory', 'first_page', 'sync', 'backup', 'import', 'clean', 'sort', 'status' )
		);

		const STATUS_DELETED = 0;
		const STATUS_ACTIVE  = 1;
		const STATUS_SOON    = 2;

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

		public static function forUser($params = array())
		{
			$query = "
				SELECT
					Services.*,
					UsersServices.*
				FROM
					users_services UsersServices
					LEFT JOIN services Services
					       ON Services.id = UsersServices.service_id
				WHERE
					UsersServices.user_id = :userId
					AND Services.status = " . static::STATUS_ACTIVE . "
				ORDER BY Services.mandatory DESC, Services.first_page DESC, Services.sort DESC
				";

			if(isset($parmas['limit']) && $params['limit']) {
				$query .= ' LIMIT ' . $params['limit'];
			}

			$services  = array();
			$result    = static::execute($query, array(':userId' => $params['id']))->group();

			if($result) {
				foreach($result as $serviceId => $service) {
					$services[$serviceId] = array_merge($service[0], array('id' => $serviceId));
				}
			}

//			$tasks = TasksModel::all(array('service_id' => array_keys($services), 'user_id' => $params['id']))->toArray();

//			if($tasks) {
//				foreach($tasks as $task) {
//					if(isset($services[$task['service_id']])) {
//						$services[$task['service_id']]['tasks'][] = $task;
//					}
//				}
//			}
			return $services;
		}

		public static function withPermissions()
		{
			$query = "
				SELECT
					Services.*
				FROM
					services Services
				WHERE
					Services.status = " . static::STATUS_ACTIVE . "
				ORDER BY sort ASC
			";

			$services  = array();
			$result    = static::execute($query)->group();

			if($result) {
				foreach($result as $serviceId => $service) {
					$services[$serviceId] = array_merge($service[0], array('id' => $serviceId));
				}
			}

			$permissions = ServicesPermissionsModel::all(array('conditions' => array('service_id' => array_keys($services), 'status' => ServicesPermissionsModel::STATUS_ACTIVE), 'order' => array('sort' => 'ASC')))->toArray();

			if($permissions) {
				foreach($permissions as $permission) {
					if(isset($services[$permission['service_id']])) {
						$services[$permission['service_id']]['permissions'][] = $permission;
					}
				}
			}

			$actions = ServicesActionsModel::all(array('conditions' => array('service_id' => array_keys($services), 'status' => ServicesActionsModel::STATUS_ACTIVE), 'order' => array('sort' => 'ASC')))->toArray();

			if($actions) {
				foreach($actions as $action) {
					if(isset($services[$action['service_id']])) {
						$services[$action['service_id']]['actions'][] = $action;
					}
				}
			}

			return $services;
		}
	}