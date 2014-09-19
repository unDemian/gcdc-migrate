<?
	/**
	 * Class Auth, used for handling oAuth authorization and authentification
	 * Also used for internal Auth management for users
	 */

	use app\models\ServicesModel;
	use app\models\UsersCredentialsModel;
	use app\models\UsersModel;
	use app\models\UsersProfilesModel;
	use app\models\UsersServicesModel;

	class Auth
	{
		public static $_errors = array(
			'oAuthGeneral'      => 'Sorry, something went wrong with you authentification.',
			'oAuthCancel'       => 'Sorry, it seems your authentification has failed, please try again.',
			'oAuthCode'         => 'Sorry, but your authorization code is invalid.',
			'oAuthToken'        => 'Sorry, but your you could not get authorize tokens from Google.',
			'oAuthTokenExpired' => 'Invalid Credentials',

			'sessionExpired'    => 'Your session has expired, please wait while we log you back in.',
		);

		/**
		 * Check login state.
		 *
		 * @param bool $redirectUrl If it's set and the user is logged in, will be redirected here
		 * @param bool $loginPage Switch for the login page, if false session is expired
		 */
		public static function checkLogin($redirectUrl = false, $loginPage = false)
		{
			if(static::_isLoggedIn()) {
				if($redirectUrl) {
					header('Location: /' . $redirectUrl);
					return;
				}
			} else {
				if( !$loginPage) {

					if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
						echo '';
						return;
					}

					header('HTTP/1.0 404 Not Found');
					static::logout(array('type' => 'warning', 'text' => static::$_errors['sessionExpired']));
					return;
				}
			}
		}

		public static function showWizard( $finish = false )
        {
			if($finish) {
				$_SESSION['current']['username']['last_login'] = date(DATE_TIME);
			}

			return ((isset($_SESSION['usernames']) && count($_SESSION['usernames']) == 1) && (!$_SESSION['current']['username']['last_login'] || $_SESSION['current']['username']['last_login'] == '0000-00-00 00:00:00'));
		}

		public static function userBeside($id)
		{
			if(count($_SESSION['usernames']) > 1) {
				foreach($_SESSION['usernames'] as $username) {
					if($username['username']['id'] != $id) {
						return $username;
					}
				}
			}
			return false;
		}

		/**
		 * Logout function
		 *
		 * @param null $error Error message for the logout
		 */
		public static function logout($params = array())
		{
			$_SESSION = array();

			if($params) {
				Util::notice($params);
			} else {
				$_SESSION['logout'] = true;
			}

			header('Location: /');
			exit;

		}

		/**
		 * Is logged in?
		 *
		 * @return bool
		 */
		private static function _isLoggedIn()
		{
			return (isset($_SESSION) && isset($_SESSION['loggedIn']));
		}

		// Logged user handling
		//////////////////////////////////////////////////////////////////////////////

		private static function _createUser($profile, $tokens, $setSession = false)
		{
			// Add username
			$username             = UsersModel::create();
			$username->google_id  = $profile['id'];
			$username->name       = $profile['displayName'];
			$username->email      = $profile['email'];
			$username->last_login = null;
			$username->status     = UsersModel::STATUS_ACTIVE;
			$username->group      = ( static::_isLoggedIn() ? $_SESSION['current']['username']['group'] : md5($profile['id']) );
			$username->save() ;

			// Credentials
			$credentials                = UsersCredentialsModel::create();
			$credentials->user_id       = $username->id;
			$credentials->access_token  = $tokens['access_token'];
			$credentials->refresh_token = (isset($tokens['refresh_token'])) ? $tokens['refresh_token'] : null;
			$credentials->token_type    = $tokens['token_type'];
			$credentials->expires_at    = date(DATE_TIME, time() + $tokens['expires_in']);
			$credentials->raw           = base64_encode(json_encode($tokens));
			$credentials->save();

			// Profile
			$userProfile           = UsersProfilesModel::create();
			$userProfile->user_id  = $username->id;
			$userProfile->type     = $profile['kind'];
			$userProfile->gender   = (isset($profile['gender']) ? $profile['gender'] : '');
			$userProfile->url      = (isset($profile['link']) ? $profile['link'] : '');
			$userProfile->avatar   = (isset($profile['picture']) ? $profile['picture'] : '');
			$userProfile->birthday = (isset($profile['birthday']) ? $profile['birthday'] : '');
			$userProfile->save();

			// Add mandatory services
			$services = ServicesModel::all(array('mandatory' => true, 'status' => ServicesModel::STATUS_ACTIVE))->column('id');

            if($services) {
                foreach($services as $service) {
                    $userService             = UsersServicesModel::create();
                    $userService->user_id    = $username->id;
                    $userService->service_id = $service;
                    $userService->save();
                }
            }

			$username    = $username->toArray();
			$userProfile = $userProfile->toArray();
			$credentials = $credentials->toArray();
			$services    = ServicesModel::forUser(array('id' => $username['id']));

			// Session thingie
			static::setUsername(compact('username', 'userProfile', 'credentials', 'services', 'setSession'));
		}

		/**
		 * Add / Edit usernames
		 *
		 * @param null  $id
		 * @param null  $current
		 * @param array $data
		 */
		public static function setUsername($data = array())
		{
			// Services
			if(isset($_SESSION['approvedServices'])) {
				UsersServicesModel::remove(array('user_id' => $data['username']['id']));

				foreach($_SESSION['approvedServices'] as $serviceId) {
					$serviceId               = intVal($serviceId);

					$userService             = UsersServicesModel::create();
					$userService->user_id    = $data['username']['id'];
					$userService->service_id = $serviceId;
					$userService->save();
				}

				unset($_SESSION['approvedServices']);
				$data['services'] = ServicesModel::forUser(array('id' => $data['username']['id']));
			}

			// Flatten data
			$newData = array();
			foreach($data as $key => $value) {
				$newData[$key] = $value;
			}
			$data = $newData;

			// When added from the wizard do not set it default
			if( !static::showWizard() || $data['setSession']) {
				$_SESSION['current'] = $data;
			}
			$_SESSION['loggedIn']    = true;

			// Update sync
			if(isset($_SESSION['wizard']['source']) && $_SESSION['wizard']['source']['username']['id'] == $newData['username']['id']) {
				$_SESSION['wizard']['source'] = $newData;
			}

			if(isset($_SESSION['wizard']['destination']) && $_SESSION['wizard']['destination']['username']['id'] == $newData['username']['id']) {
				$_SESSION['wizard']['destination']['source'] = $newData;
			}

			// Group Users
			$users    = UsersModel::all(array( 'group' => $data['username']['group'] ))->toArray();
			if($users) {
				$_SESSION['usernames'] = array();
				foreach($users as $user) {
					$_SESSION['usernames'][$user['id']] = UsersModel::profile($user['id']);
				}
			}
		}


		// oAuth
		//////////////////////////////////////////////////////////////////////////////

		public static function oAuthScopes()
		{
			return implode(' ', ServicesModel::all(array('mandatory' => true, 'status' => ServicesModel::STATUS_ACTIVE))->column('scopes'));
		}

		public static function oAuthTokensLogin($tokens = false)
		{
			$profile     = Rest::get('https://www.googleapis.com/oauth2/v1/userinfo', array('alt' => 'json', 'access_token' => $tokens['access_token']));
			$username    = UsersModel::first(array('google_id' => $profile['id'], 'status' => UsersModel::STATUS_ACTIVE)) ?: UsersModel::create();
			$redirectUrl = BASE_URL . 'dashboard';

			// Existing (if services > 1 refresh token) + update last_login + update credentials
			if($username->id) {

				// Credentials update
				$credentials = UsersCredentialsModel::first(array('user_id' => $username->id));

				// Refresh token if needed
				$services = ServicesModel::forUser(array('id' => $username->id));
				if(count($services) > 1) {
					$credentialsArray['username']['id'] = $username->id;
					$credentialsArray['credentials']    = $credentials->toArray();
					$tokens = static::oAuthRefreshToken($credentialsArray, false, 'force');
				}

				$credentials->access_token = $tokens['access_token'];
				$credentials->expires_at   = date(DATE_TIME, time() + $tokens['expires_in']);
				$credentials->save();

				$credentials = $credentials->toArray();

				// User profile
				$userProfile = UsersProfilesModel::first(array('user_id' => $username->id))->toArray();

				// Username update
				$username->last_login = date(DATE_TIME);
				$username->save();
				$username = $username->toArray();

				$setSession = true;

				// Session thingie
				static::setUsername(compact('username', 'services', 'credentials', 'userProfile', 'setSession'));

			// New User
			} else {

				// Get refresh token
				$data = array (
					'client_id'     => OAUTH_CLIENT_ID,
					'client_secret' => OAUTH_CLIENT_SECRET,
					'redirect_uri'  => 'postmessage',
					'code'          => $tokens['code'],
					'grant_type'    => 'authorization_code',
				);

				$tokens               = Rest::post('https://accounts.google.com/o/oauth2/token', $data);
				$tokens['expires_at'] = date(DATE_TIME, time() + $tokens['expires_in']);
				$gplus                = Rest::get('https://www.googleapis.com/plus/v1/people/me', array(), array('credentials' => $tokens));
				$profile              = array_merge($profile, $gplus);

				static::_createUser($profile, $tokens, 'setSession');
			}

			return array('success' => 'true', 'redirectUrl' => $redirectUrl);
		}

		public static function oAuthTokensAdd($tokens = false)
		{
			$profile     = Rest::get('https://www.googleapis.com/oauth2/v1/userinfo', array('alt' => 'json', 'access_token' => $tokens['access_token']));
			$username    = UsersModel::first(array('google_id' => $profile['id'], 'status' => UsersModel::STATUS_ACTIVE)) ?: UsersModel::create();

			if( !$username->id) {
				if( static::showWizard()) {
					$redirectUrl = BASE_URL . 'migrate';

					// Last login update
					$username->last_login = date(DATE_TIME);
					$username->save();

				} else {
					$redirectUrl = BASE_URL . 'accounts';
				}
				Util::notice(array('type' => 'success', 'text' => 'You have successfully added the ' . $profile['email'] . ' account.'));

				// Get refresh token
				$data = array (
					'client_id'     => OAUTH_CLIENT_ID,
					'client_secret' => OAUTH_CLIENT_SECRET,
					'redirect_uri'  => 'postmessage',
					'code'          => $tokens['code'],
					'grant_type'    => 'authorization_code',
				);

				$tokens               = Rest::post('https://accounts.google.com/o/oauth2/token', $data);
				$tokens['expires_at'] = date(DATE_TIME, time() + $tokens['expires_in']);
				$gplus                = Rest::get('https://www.googleapis.com/plus/v1/people/me', array(), array('credentials' => $tokens));
				$profile              = array_merge($profile, $gplus);

				static::_createUser($profile, $tokens, false);
			} else {
				Util::notice(array('type' => 'warning', 'text' => 'The selected account is already linked to our application.'));
				$redirectUrl = BASE_URL . 'accounts';
			}


			return array('success' => 'true', 'redirectUrl' => $redirectUrl);
		}

		public static function oAuthTokensPermissions($tokens = false)
		{
			$profile     = Rest::get('https://www.googleapis.com/oauth2/v1/userinfo', array('alt' => 'json', 'access_token' => $tokens['access_token']));
			$username    = UsersModel::first(array('google_id' => $profile['id'], 'status' => UsersModel::STATUS_ACTIVE)) ?: UsersModel::create();

			if( !static::showWizard()) {
				$redirectUrl = BASE_URL . 'accounts/permissions';
			} else {
				$redirectUrl = BASE_URL . 'accounts/add';
			}
			Util::notice(array('type' => 'success', 'text' => 'You have successfully updated your ' . $username->email . ' account permissions.'));

			$username->last_login = $_SESSION['current']['username']['last_login'];
			$username->save();

			// Credentials update
			$credentials = UsersCredentialsModel::first(array('user_id' => $username->id));

			// Get refresh token
			$data = array (
				'client_id'     => OAUTH_CLIENT_ID,
				'client_secret' => OAUTH_CLIENT_SECRET,
				'redirect_uri'  => 'postmessage',
				'code'          => $tokens['code'],
				'grant_type'    => 'authorization_code',
			);

			$tokens = Rest::post('https://accounts.google.com/o/oauth2/token', $data);

			$credentials->refresh_token = $tokens['refresh_token'];
			$credentials->access_token  = $tokens['access_token'];
			$credentials->expires_at    = date(DATE_TIME, time() + $tokens['expires_in']);
			$credentials->save();

			$services    = UsersServicesModel::all(array('user_id' => $username->id))->toArray();
			$credentials = $credentials->toArray();

			// User profile
			$userProfile = UsersProfilesModel::first(array('user_id' => $username->id))->toArray();
			$username    = $username->toArray();

			$setSession  = true;

			// Session thingie
			static::setUsername(compact('username', 'services', 'credentials', 'userProfile', 'setSession'));

			return array('success' => 'true', 'redirectUrl' => $redirectUrl);
		}

		public static function oAuthRefreshToken($user = array(), $updateSession = false, $force = false)
		{
			$expires = time() + 1000;
			if(isset($user['credentials']['expires_at'])) {
				$expires = strtotime($user['credentials']['expires_at']);
			}

			if(($expires < time()) || $force) {

				$payload = array(
					'client_id'     => OAUTH_CLIENT_ID,
					'client_secret' => OAUTH_CLIENT_SECRET,
					'grant_type'    => 'refresh_token',
					'refresh_token' => $user['credentials']['refresh_token']
				);

				$refreshedToken = Rest::post('https://accounts.google.com/o/oauth2/token', $payload);

				if($refreshedToken) {

					// Credentials
					$credentials               = UsersCredentialsModel::first(array('user_id' => $user['username']['id']));
					$credentials->access_token = $refreshedToken['access_token'];
					$credentials->token_type   = $refreshedToken['token_type'];
					$credentials->expires_at   = date(DATE_TIME, time() + $refreshedToken['expires_in']);
					$credentials->save();

					// Refresh session data
					if($updateSession) {
						$_SESSION['usernames'][$user['username']['id']]['credentials']['access_token'] = $credentials->access_token;
						$_SESSION['usernames'][$user['username']['id']]['credentials']['expires_at'] = $credentials->expires_at;

						if($_SESSION['current']['username']['id'] == $user['username']['id']) {
							$_SESSION['current'] = $_SESSION['usernames'][$user['username']['id']];
						}
					}

					$user['credentials']['access_token'] = $credentials->access_token;
					$user['credentials']['expires_at'] = $credentials->expires_at;
				}

				return $refreshedToken;
			}

			return $user;
		}
	}