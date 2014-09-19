<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	// Controllers & Models
	use app\models\ServicesModel;
	use app\models\IntroModel;
	use app\models\UsersModel;
	use app\models\UsersServicesModel;
	use app\models\TasksModel;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Accounts extends \Controller
	{
		/**
		 * Listing accounts
		 */
		public static function index()
		{
			$templateData = array(
				'template' => 'accounts/content',
				'title'    => 'Migrate - Accounts Management',
				'bodyId'   => 'accounts',
				'styles'   => array( 'accounts.css' ),
				'scripts'  => array( 'accounts.js' ),
			);

			\Render::layout('template', $templateData);
		}

		public static function add()
		{
			$templateData = array(
				'template' => 'accounts/permissions',
				'title'    => 'Migrate - Add Account',
				'bodyId'   => 'accounts-add',
				'styles'   => array( 'accounts.css' ),
				'scripts'  => array( 'accounts.js' ),

				// Specific
				'heading'          => 'Connect another account',
				'description'      => 'Select which services you want to be authorized for the new account and then hit the Authorization button in order to select which google account you want to link with our application. Remember that you can revoke access to this account at any time.',

				'services'         => ServicesModel::withPermissions(),
				'servicesSoon'     => ServicesModel::all(array('status' => ServicesModel::STATUS_SOON))->toArray(),
				'selectedServices' => array(),

				'skipUrl'          => 'dashboard/1',
				'step'             => 2,
			);

			\Render::layout('template', $templateData);
		}

		public static function details($accountKey = false)
		{
			if($accountKey) {
				$account = $_SESSION['usernames'][$accountKey];
			} else {
				$account = $_SESSION['current'];
			}

			$templateData = array(
				'template' => 'accounts/details',
				'title'    => 'Migrate - Account details',
				'bodyId'   => 'accounts-details',
				'styles'   => array( 'accounts.css' ),
				'scripts'  => array( 'accounts.js' ),

				// Specific
				'account'          => $account,
				'services'         => ServicesModel::withPermissions(),
				'selectedServices' => UsersServicesModel::all(array('user_id' => $account['username']['id']))->column('service_id'),
				'tasks'            => TasksModel::listingFor(array('user_id' => $account['username']['id'], 'user_affected_id' => $account['username']['id'])),
				'intro'       => (bool) IntroModel::first(array('page' => 'profile', 'group' => $_SESSION['current']['username']['group']))
			);

			if( !$templateData['intro'] ) {
				IntroModel::create(array('page' => 'profile', 'group' => $_SESSION['current']['username']['group']))->save();
			}

			\Render::layout('template', $templateData);
		}

		public static function permissions()
		{
			$templateData = array(
				'template' => 'accounts/permissions',
				'title'    => 'Migrate - Permissions',
				'bodyId'   => 'accounts-update',
				'styles'   => array( 'accounts.css' ),
				'scripts'  => array( 'accounts.js' ),

				// Specific
				'heading'          => 'Select your services',
				'description'      => 'These are the current available services that can be used in our operations. You can select any time which services are we allowed to use. If you roll-over a service name you can see all the permissions and what action can we performe.',

				'services'         => ServicesModel::withPermissions(),
				'servicesSoon'     => ServicesModel::all(array('status' => ServicesModel::STATUS_SOON))->toArray(),
				'selectedServices' => UsersServicesModel::all(array('user_id' => $_SESSION['current']['username']['id']))->column('service_id'),

				'skipUrl'          => 'accounts/add',
				'step'             => 1,
			);

			\Render::layout('template', $templateData);
		}

		/**
		 * Select account
		 *
		 * @param int $key Account session key
		 */
		public static function select($key = 0)
		{
			if(isset($_SESSION['usernames'][$key])) {
				$_SESSION['current']                           = $_SESSION['usernames'][$key];
				$_SESSION['current']['username']['last_login'] = date(DATE_TIME);

				$_SESSION['wizard']['source'] = $_SESSION['current'];

				if(isset($_SESSION['wizard']['destination']) && count($_SESSION['usernames']) > 1) {
					$_SESSION['wizard']['destination'] = \Auth::userBeside($_SESSION['current']['username']['id']);
				}

				// Refresh data
				$_SESSION['share'] = array();
				$_SESSION['clean'] = array();
			}

			// Check token
			\Auth::oAuthRefreshToken($_SESSION['current'], 'updateSession');

			\Router::redirect(\Router::referer());
		}

		/**
		 * Unlink account
		 *
		 * @param int $key Account session key
		 */
		public static function unlink($key = 0)
		{
			// Revoke Token
			\Rest::get('https://accounts.google.com/o/oauth2/revoke', array('token' => $_SESSION['usernames'][$key]['credentials']['access_token']) );

			// Remove Data
			UsersModel::remove($_SESSION['usernames'][$key]['username']['id']);

			if(isset($_SESSION['usernames'][$key])) {

				$toRemove = $_SESSION['usernames'][$key];

				unset($_SESSION['usernames'][$key]);

				if(isset($_SESSION['wizard']['source']) && $_SESSION['wizard']['source']['username']['id'] == $toRemove['username']['id']) {
					$_SESSION['wizard']['source'] = \Auth::userBeside($_SESSION['wizard']['destination']);
				}

				if(isset($_SESSION['wizard']['destination']) && $_SESSION['wizard']['destination']['username']['id'] == $toRemove['username']['id']) {
					$_SESSION['wizard']['destination'] = \Auth::userBeside($_SESSION['sync']['source']);
				}

				if($_SESSION['current']['username']['id'] == $key && count($_SESSION['usernames'])) {
					end($_SESSION['usernames']);
					$_SESSION['current'] = current($_SESSION['usernames']);
				}
			}

			if( !count($_SESSION['usernames'])) {
				\Auth::logout();
			}

			\Util::notice(array('type' => 'success', 'text' => 'The account has been successfully removed and its API tokens revoked.' ));
			\Router::redirect('accounts');
		}

		public static function services()
		{
			if(isset($_POST['ids'])) {
				$_SESSION['approvedServices'] = $_POST['ids'];
			}

		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Accounts');