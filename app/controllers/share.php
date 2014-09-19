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
	use app\models\IntroModel;
	use app\models\TasksModel;
	use app\models\TasksServicesModel;
	use app\models\MigratedDataModel;
	use \google\appengine\api\taskqueue\PushTask;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Share extends \Controller
	{
		/**
		 * Listing accounts
		 */
		public static function index()
		{
			// Share Init
			if( ! isset($_SESSION['share']) || !$_SESSION['share']) {

				// Services
				next($_SESSION['current']['services']);
				$first = current($_SESSION['current']['services']);
				$_SESSION['share']['service'] = $first['service_id'];

				// Data
				$_SESSION['share']['selectedData'] = array();
			}

			$templateData = array(
				'template' => 'share/content',
				'title'    => 'Migrate - Share',
				'bodyId'   => 'share',
				'styles'   => array(
					'common/wizard.css',
					'share/share.css'
				),
				'scripts'  => array(
					'clipboard/jquery.clipboard.js',
					'common/wizard.js',
					'share/share.js'
				),
				'services' => $_SESSION['current']['services'],
				'polling'     => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_SHARE, 'status' => array(TasksModel::STATUS_PROGRESS, TasksModel::STATUS_SCHEDULED))) ? 'yes' : 'no',
				'tasks'       => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_SHARE)),
				'intro'       => (bool) IntroModel::first(array('page' => 'share', 'group' => $_SESSION['current']['username']['group']))
			);

			if( !$templateData['intro'] ) {
				IntroModel::create(array('page' => 'share', 'group' => $_SESSION['current']['username']['group']))->save();
			}

			\Render::layout('template', $templateData);
		}

		public static function feed()
		{
			$tasks = TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_SHARE));
			if($tasks) {
				$output = \Render::view('share/table', compact('tasks'), 'return');
			} else {
				$output = \Render::view('common/empty', false, 'return');
			}

			echo $output;
		}

		public static function start()
		{
			// Create task
			$task = TasksModel::create();

			$task->user_id          = $_SESSION['current']['username']['id'];
			$task->user_affected_id = $_SESSION['current']['username']['id'];
			$task->group_id         = $_SESSION['current']['username']['group'];
			$task->type             = TasksModel::TYPE_SHARE;
			$task->title            = 'Share Data';
			$task->created_at       = date(DATE_TIME);
			$task->estimate         = 10 * 60;
			$task->save();

			// Add services to task
			if(isset($_SESSION['share']) && $_SESSION['share']['service']) {
				$serviceId = (int) $_SESSION['share']['service'];
				TasksServicesModel::create(array('task_id' => $task->id, 'service_id' => $serviceId))->save();
				$task->save();
			}

			// Create share
			$share = SharesModel::create();

			$share->task_id    = $task->id;
			$share->user_id    = $_SESSION['current']['username']['id'];
			$share->service_id = $_SESSION['share']['service'];
			$share->title      = 'Share Data';
			$share->data       = json_encode($_SESSION['share']['selectedData']);
			$share->link       = sha1($task->user_id . time());
			$share->expires    = 86400;
			$share->created_at = date(DATE_TIME);
			$share->status     = SharesModel::STATUS_ACTIVE;
			$share->save();

			// Reset share
			unset($_SESSION['share']);

			\Util::notice(array('type' => 'success', 'text' => 'The sharing process has started. Check the list below to review it\'s status.'));

			$task = new PushTask('/queue/add/' . $task->id);
			$task->add();
		}

		public static function remove($taskId)
		{
			TasksModel::remove($taskId);
			SharesModel::remove(array('task_id' => $taskId));
			MigratedDataModel::softDelete(array('sync_task_id' => $taskId));

			\Util::notice(array('type' => 'success', 'text' => 'The task was successfully deleted. The data isn\'t shared anymore.'));
			\Router::redirect('share');
		}

		public static function details($id)
		{
			$templateData = array(
				'template'    => 'share/details',
				'title'       => 'Share - Details',
				'bodyId'      => 'Share',
				'styles'      => array(
					'common/wizard.css',
					'share/details.css'
				),
				'scripts'     => array(
					'common/wizard.js',
					'share/details.js',
				),
				'task'    => TasksModel::details($id),
			);

			$templateData['source']      = $_SESSION['usernames'][$templateData['task']['user_id']];
			$templateData['destination'] = $_SESSION['usernames'][$templateData['task']['user_affected_id']];

			\Render::layout('template', $templateData);
		}

		public static function detail()
		{
			$taskId = (int)$_POST['id'];
			$task = TasksModel::details($taskId);
			if($task['services']) {
				foreach($task['services'] as $service) {
					if($service['id'] == $_POST['type']) {
						$_service = $service;
					}
				}
			}

			// Migrated Data
			$migratedData = array();
			$share = SharesModel::first(array('task_id' => $task['id']));

			if($share) {
				$share = $share->toArray();
				$migratedData  = json_decode($share['data'], true);

				$service = ServicesModel::first($share['service_id'])->toArray();
				$backups = call_user_func_array(array('app\\libraries\\' . $service['library'], 'shared'), array($task));
			}
			echo \Render::view('share/details/' . strtolower($_service['name']), array('task' => $task, 'service' => $_service, 'migratedData' => $migratedData, 'data' => $backups), 'return');
		}

		// Wizard
		public static function selectService()
		{
			$_SESSION['share']['service'] = (int) $_POST['id'];
		}

		public static function data()
		{
			$service = ServicesModel::first($_SESSION['share']['service'])->toArray();

			if( !isset($_SESSION['share']['data']) || empty($_SESSION['share']['data'])) {
				\Auth::oAuthRefreshToken($_SESSION['current'], 'updateSession', 'force');
				$_SESSION['share']['data'] = call_user_func_array(array('app\\libraries\\' . $service['library'], 'share'), array($_SESSION['current']));
			}

			$data = $_SESSION['share']['data'];

			echo \Render::view('share/data', array('data' => $data, 'selectedData' => $_SESSION['share']['selectedData']), 'return');
		}

		public static function selectData()
		{
			$id = $_POST['id'];
			$type = $_POST['type'];

			if($_POST['action'] == 'select') {
				$_SESSION['share']['selectedData'][$type][$id] = $id;
			} else {
				if(isset($_SESSION['share']['selectedData'][$type][$id])) {
					unset($_SESSION['share']['selectedData'][$type][$id]);
				}
			}
		}

		public static function finish()
		{
			echo \Render::view('share/finish', array('services' => $_SESSION['current']['services'], 'selectedService' => $_SESSION['share']['service'], 'data' => $_SESSION['share']['data'], 'selectedData' => $_SESSION['share']['selectedData']), 'return');
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Share');