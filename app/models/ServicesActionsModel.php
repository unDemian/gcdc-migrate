<?
	namespace app\models;

	class ServicesActionsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'services_actions',
			'fields' => array( 'id', 'service_id', 'content', 'sort', 'status' )
		);

		const STATUS_DELETED = 0;
		const STATUS_ACTIVE  = 1;

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

		public static function getActions($serviceId)
		{
			$result  = array();
			$actions = static::all(array('conditions' => array('service_id' => $serviceId, 'status' => static::STATUS_ACTIVE), 'order' => array('service_id' => 'ASC', 'sort' => 'ASC')))->toArray();

			if($actions) {
				foreach($actions as $action) {
					$result[] = $action['content'];
				}
			}

			return $result;
		}
	}