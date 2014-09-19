<?
	namespace app\models;

	class UsersProfilesModel extends \Model
	{
		public static $schema = array(
			'table'  => 'users_profiles',
			'fields' => array( 'id', 'user_id', 'type', 'gender', 'url', 'avatar', 'birthday' )
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