<?
	namespace app\controllers\auth;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	/**
	 * Handle oAuth response
	 *
	 * @package app\controllers\auth
	 */
	class oAuthHandler
	{
		public static function login()
		{
			if((isset($_POST) && (isset($_POST['error'])) || !isset($_POST['access_token']))) {

				if(isset($_POST['error']) && $_POST['error'] != 'immediate_failed') {
					\Util::notice(array('type' => 'danger', 'text' => 'Sorry, operation failed with the following error: ' . $_POST['error']));
				} else {
					echo json_encode( array( 'error' => 'immediate_failed') );
				}

			} else {

				if(isset($_POST['referrer']) && in_array($_POST['referrer'], array('login', 'add', 'permissions'))) {
					echo json_encode( call_user_func_array(array('\Auth', 'oAuthTokens' . ucfirst($_POST['referrer'])), array($_POST)) );
				} else {
					echo json_encode( array( 'error' => 'Invalid referrer!') );
				}
			}
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'oAuthHandler');