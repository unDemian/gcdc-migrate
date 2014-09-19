<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	// Controllers & Models
	use app\models\IntroModel;
	use app\models\TasksModel;
	use app\models\ServicesModel;

	/**
	 * Dashboard page
	 *
	 * @package app\controllers
	 */
	class Dashboard extends \Controller
	{
		/**
		 * Dashboard index page
		 */
		public static function index($finish = false)
		{
			if($finish) {
				\Auth::showWizard('finish');
			} else {
				if(\Auth::showWizard()) {
					\Router::redirect('accounts/permissions');
				}
			}

			$templateData = array(
				'template' => 'dashboard/content',
				'title'    => 'Migrate - Dashboard',
				'bodyId'   => 'dashboard',
				'styles'   => array(
					'dashboard.css'
				),
				'scripts'  => array( ),

				// Specific
				'queue'     => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id']), 4),
				'services'  => ServicesModel::forUser(array('id' => $_SESSION['current']['username']['id'], 'limit' => 4)),
				'usernames' => $_SESSION['usernames'],
				'intro'     => (bool) IntroModel::first(array('page' => 'dashboard', 'group' => $_SESSION['current']['username']['group']))
			);

			if( !$templateData['intro'] ) {
				IntroModel::create(array('page' => 'dashboard', 'group' => $_SESSION['current']['username']['group']))->save();
			}

			\Render::layout('template', $templateData);
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Dashboard');
