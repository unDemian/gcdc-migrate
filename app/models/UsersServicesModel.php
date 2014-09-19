<?
	namespace app\models;

	class UsersServicesModel extends \Model
	{
		public static $schema = array(
			'table'  => 'users_services',
			'fields' => array( 'id', 'user_id', 'service_id' )
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