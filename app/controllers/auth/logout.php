<?
	namespace app\controllers\auth;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	/**
	 * Class Logout
	 */
	class Logout extends \Controller
	{
		public static function index()
		{
			\Auth::logout();
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Logout');
