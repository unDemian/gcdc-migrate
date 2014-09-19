<?
	namespace app\models;

	class SharesModel extends \Model
	{
		public static $schema = array(
			'table'  => 'shares',
			'fields' => array( 'id', 'task_id', 'user_id', 'service_id', 'title', 'data', 'link', 'expires', 'created_at', 'status' )
		);

		const STATUS_ACTIVE  = 1;
		const STATUS_EXPIRED = 0;

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