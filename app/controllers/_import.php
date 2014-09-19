<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	// Appengine
	require_once 'google/appengine/api/taskqueue/PushTask.php';

	// Controllers & Models
	use app\models\TasksModel;
	use app\models\TasksServicesModel;
	use \google\appengine\api\taskqueue\PushTask;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Import extends \Controller
	{
		/**
		 * Listing accounts
		 */
		public static function index($selectedService = 0)
		{
			$templateData = array(
				'template' => 'import/content',
				'title'    => 'Migrate - Import',
				'bodyId'   => 'import',
				'styles'   => array(
					'import.css'
				),
				'scripts'  => array(),
				'history'  => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_IMPORT)),
				'services' => $_SESSION['current']['services'],
			);

			\Render::layout('template', $templateData);
		}

		public static function start()
		{
			// Create task
			$task = TasksModel::create();

			$task->user_id          = $_SESSION['current']['username']['id'];
			$task->user_affected_id = $_SESSION['current']['username']['id'];
			$task->group_id         = $_SESSION['current']['username']['group'];
			$task->type             = TasksModel::TYPE_BACKUP;
			$task->title            = 'Backup Data';
			$task->contains         = json_encode(array( 'Youtube Videos', 'Youtube Playlists', 'Youtube Subscriptions' ));
			$task->created_at       = date(DATE_TIME);
			$task->estimate         = 10 * 60;
			$task->status           = TasksModel::STATUS_PROGRESS;
			$task->save();

			// Add services
			if($_POST['services']) {
				foreach($_POST['services'] as $serviceId) {
					$serviceId = (int) $serviceId;
					$taskService = TasksServicesModel::create(array('task_id' => $task->id, 'service_id' => $serviceId))->save();
				}
			}

			\Util::notice(array('type' => 'success', 'text' => 'The backup process has started. Check the list below to review its status.'));

			$task = new PushTask('/queue/backup/' . $task->id);
			$task->add();
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Import');