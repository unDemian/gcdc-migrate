<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	// Appengine
	require_once 'google/appengine/api/taskqueue/PushTask.php';

	// Controllers & Models
	use app\models\ServicesModel;
	use app\models\SharesModel;
	use app\models\TasksModel;
	use app\models\TasksServicesModel;
	use app\models\UsersModel;
	use \google\appengine\api\taskqueue\PushTask;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Shared
	{
		/**
		 * Listing accounts
		 */
		public static function link($link = '')
		{
			$share = array();
			$data = array();
			$task = array();
			$service = array();
			$backups = array();
			$profile = array();

			$share = SharesModel::first(array('link' => $link));

			if($share) {
				$share = $share->toArray();
				$data  = json_decode($share['data'], true);

				if(strtotime($share['created_at']) + $share['expires'] > time()) {
					$task    = TasksModel::first($share['task_id'])->toArray();
					$service = ServicesModel::first($share['service_id'])->toArray();
					$profile = UsersModel::profile($task['user_id']);
					$backups = call_user_func_array(array('app\\libraries\\' . $service['library'], 'shared'), array($task));
				} else {
					\Util::notice(array('type' => 'danger', 'text' => 'The requested link has expired.'));
				}
			} else {
				\Util::notice(array('type' => 'danger', 'text' => 'The requested link does not exist.'));
			}

			$templateData = array(
				'template' => 'shared/content',
				'title'    => 'Migrate - Shared Data',
				'bodyId'   => 'shared',
				'styles'   => array(
					'shared.css'
				),
				'scripts'  => array(
					'common/request.js',
					'shared.js'
				),
				'share'   => $share,
				'data'    => $data,
				'task'    => $task,
				'service' => $service,
				'backups' => $backups,
				'profile' => $profile,
			);

			\Render::layout('template', $templateData);
		}

		public static function save()
		{
			if((isset($_POST) && (isset($_POST['error'])) || !isset($_POST['access_token']))) {

				if(isset($_POST['error']) && $_POST['error'] != 'immediate_failed') {
					\Util::notice(array('type' => 'danger', 'text' => 'Sorry, operation failed with the following error: ' . $_POST['error']));
				} else {
					echo json_encode( array( 'error' => 'immediate_failed') );
				}

			} else {

				$share = SharesModel::first(array('link' => $_POST['task']));

				if($share) {
					$share = $share->toArray();
					$data  = json_decode($share['data'], true);

					if(strtotime($share['created_at']) + $share['expires'] > time()) {
						$task    = TasksModel::first($share['task_id'])->toArray();
						$service = ServicesModel::first($share['service_id'])->toArray();

						$source = UsersModel::profile($task['user_id']);

						$destination['username']['id'] = uniqid();
						$destination['credentials'] = $_POST;

						call_user_func_array(array('app\\libraries\\' . $service['library'], 'backup'), array($destination, 0, $task['id']));

						call_user_func_array(array('app\\libraries\\' . $service['library'], 'migrate'), array($source, $destination, $task['id'], false, $data));

						\Util::notice(array('type' => 'success', 'text' => 'The data is being imported. Please check your account in a couple of minutes.'));

					} else {
						\Util::notice(array('type' => 'danger', 'text' => 'The requested link has expired.'));
					}
				}
			}
		}

	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Shared');