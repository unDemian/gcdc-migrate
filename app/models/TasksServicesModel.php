<?
	namespace app\models;

	class TasksServicesModel extends \Model
	{
		public static $schema = array(
			'table'  => 'tasks_services',
			'fields' => array( 'id', 'task_id', 'service_id', 'stats' )
		);

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
	}