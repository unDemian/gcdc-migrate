<?
	namespace app\models;

	use app\models\UsersProfilesModel;
	use app\models\UsersServicesModel;
	use app\models\UsersCredentialsModel;

	class UsersModel extends \Model
	{
		public static $schema = array(
			'table'  => 'users',
			'fields' => array( 'id', 'google_id', 'group', 'name', 'email', 'last_login', 'created', 'modified', 'status' )
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

		public static function profile($userId)
		{
			$username    = static::first($userId)->toArray();
			$userProfile = UsersProfilesModel::first(array('user_id' => $userId))->toArray();
			$credentials = UsersCredentialsModel::first(array('user_id' => $userId))->toArray();
			$services    = ServicesModel::forUser(array('id' => $userId));

			return compact('username', 'userProfile', 'credentials', 'services');
		}

		public static function withCredentials()
		{
			$usernames = static::all()->toArray();
			if($usernames) {
				foreach($usernames as $key => $user) {
					$username    = static::first($user['id'])->toArray();
					$userProfile = UsersProfilesModel::first(array('user_id' => $user['id']));
					if($userProfile) {
						$userProfile = $userProfile->toArray();
					}
					$credentials = UsersCredentialsModel::first(array('user_id' => $user['id']));
					if($credentials) {
						$credentials = $credentials->toArray();
					}
					$services    = ServicesModel::forUser(array('id' => $user['id']));

					$usernames[$key] = compact('username', 'userProfile', 'credentials', 'services');
				}
			}

			return $usernames;
		}

		public static function remove($id = null)
		{
			parent::remove($id);

			UsersProfilesModel::remove(array( 'user_id' => $id ));
			UsersServicesModel::remove(array( 'user_id' => $id ));
			UsersCredentialsModel::remove(array( 'user_id' => $id ));
		}
	}