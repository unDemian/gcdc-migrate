<?
	namespace app\models;

	class UsersCredentialsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'users_credentials',
			'fields' => array( 'id', 'user_id', 'access_token', 'refresh_token', 'token_type', 'expires_at', 'raw' )
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