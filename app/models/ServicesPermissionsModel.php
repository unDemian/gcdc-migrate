<?
	namespace app\models;

	class ServicesPermissionsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'services_permissions',
			'fields' => array( 'id', 'service_id', 'title', 'description', 'read', 'write', 'sort', 'status' )
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
	}