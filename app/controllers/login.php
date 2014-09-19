<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	/**
	 * Class Login
	 * Login page handler
	 *
	 * @package app\controllers
	 */
	class Login
	{
		/**
		 * Login Page
		 */
		public static function index()
		{
			$templateData = array(
				'template' => 'login/content',
				'title'    => 'Migrate Google Data',
				'bodyId'   => 'login',
				'styles'   => array(
					'login.css'
				),
				'scripts'  => array(
					'login.js'
				),
			);

			\Render::layout('template', $templateData);
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Login', array('loginPage' => true, 'loginRedirect' => 'dashboard'));


