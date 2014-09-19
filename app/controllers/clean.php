<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	// Appengine
	require_once 'google/appengine/api/taskqueue/PushTask.php';

	// Controllers & Models
	use app\models\ServicesModel;
	use app\models\MigratedDataModel;
	use app\models\SharesModel;
	use app\models\IntroModel;
	use app\models\TasksModel;
	use app\models\TasksServicesModel;
	use \google\appengine\api\taskqueue\PushTask;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Clean extends \Controller
	{
		/**
		 * Listing accounts
		 */
		public static function index($selectedService = 0)
		{
			// Clean Init
			if( ! isset($_SESSION['clean']) || !$_SESSION['clean']) {

				// Services
				next($_SESSION['current']['services']);
				$first = current($_SESSION['current']['services']);
				$_SESSION['clean']['service'] = $first['service_id'];

				// Data
				$_SESSION['clean']['selectedData'] = array();
			}

			$templateData = array(
				'template' => 'clean/content',
				'title'    => 'Migrate - Clean',
				'bodyId'   => 'clean',
				'styles'   => array(
					'common/wizard.css',
					'clean/clean.css'
				),
				'scripts'  => array(
					'common/wizard.js',
					'clean/clean.js'
				),
				'services'    => $_SESSION['current']['services'],
				'polling'     => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_CLEAN, 'status' => array(TasksModel::STATUS_SCHEDULED, TasksModel::STATUS_PROGRESS, TasksModel::STATUS_REVERTING))) ? 'yes' : 'no',
				'tasks'       => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_CLEAN)),
				'intro'       => (bool) IntroModel::first(array('page' => 'clean', 'group' => $_SESSION['current']['username']['group']))
			);

			if( !$templateData['intro'] ) {
				IntroModel::create(array('page' => 'clean', 'group' => $_SESSION['current']['username']['group']))->save();
			}

			\Render::layout('template', $templateData);
		}

		public static function feed()
		{
			$tasks = TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'type' => TasksModel::TYPE_CLEAN));
			if($tasks) {
				$output = \Render::view('clean/table', compact('tasks'), 'return');
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
			$task->type             = TasksModel::TYPE_CLEAN;
			$task->title            = 'Clean Data';
			$task->created_at       = date(DATE_TIME);
			$task->estimate         = 10 * 60;
			$task->save();

			// Add services to task
			if(isset($_SESSION['clean']) && $_SESSION['clean']['service']) {
				$serviceId = (int) $_SESSION['clean']['service'];
				TasksServicesModel::create(array('task_id' => $task->id, 'service_id' => $serviceId))->save();
				$task->save();
			}

			// Create share
			$share = SharesModel::create();

			$share->task_id    = $task->id;
			$share->user_id    = $_SESSION['current']['username']['id'];
			$share->service_id = $_SESSION['clean']['service'];
			$share->title      = 'Clean Data';
			$share->data       = json_encode($_SESSION['clean']['selectedData']);
			$share->link       = sha1($task->user_id . time());
			$share->expires    = 86400;
			$share->created_at = date(DATE_TIME);
			$share->status     = SharesModel::STATUS_ACTIVE;
			$share->save();

			// Reset clean
			unset($_SESSION['clean']);

			\Util::notice(array('type' => 'success', 'text' => 'The cleaning process has started. Check the list below to review it\'s status.'));

			$task = new PushTask('/queue/add/' . $task->id);
			$task->add();
		}

		public static function revert($taskId)
		{
			$task = new PushTask('/queue/add/' . $taskId . '/revert');
			$task->add();

			// Notification
			\Util::notice(array('type' => 'success', 'text' => 'Your cleaning process is now beeing reverted. Check the list below to see it\'s status.'));
			\Router::redirect('clean');
		}

		public static function details($id)
		{
			$templateData = array(
				'template'    => 'clean/details',
				'title'       => 'Clean - Details',
				'bodyId'      => 'clean',
				'styles'      => array(
					'common/wizard.css',
					'clean/details.css'
				),
				'scripts'     => array(
					'common/wizard.js',
					'clean/details.js',
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
			echo \Render::view('clean/details/' . strtolower($_service['name']), array('task' => $task, 'service' => $_service, 'migratedData' => $migratedData, 'data' => $backups), 'return');
		}

		// Wizard
		public static function selectService()
		{
			$_SESSION['clean']['service'] = (int) $_POST['id'];
		}

		public static function data()
		{
			$service = ServicesModel::first($_SESSION['clean']['service'])->toArray();

			if( !isset($_SESSION['clean']['data']) || empty($_SESSION['clean']['data'])) {
				\Auth::oAuthRefreshToken($_SESSION['current'], 'updateSession', 'force');
				$_SESSION['clean']['data'] = call_user_func_array(array('app\\libraries\\' . $service['library'], 'share'), array($_SESSION['current']));
			}

			$data = $_SESSION['clean']['data'];

			echo \Render::view('clean/data', array('data' => $data, 'selectedData' => $_SESSION['clean']['selectedData']), 'return');
		}

		public static function selectData()
		{
			$id = $_POST['id'];
			$type = $_POST['type'];

			if($_POST['action'] == 'select') {
				$_SESSION['clean']['selectedData'][$type][$id] = $id;
			} else {
				if(isset($_SESSION['clean']['selectedData'][$type][$id])) {
					unset($_SESSION['clean']['selectedData'][$type][$id]);
				}
			}
		}

		public static function finish()
		{
			echo \Render::view('clean/finish', array('services' => $_SESSION['current']['services'], 'selectedService' => $_SESSION['clean']['service'], 'data' => $_SESSION['clean']['data'], 'selectedData' => $_SESSION['clean']['selectedData']), 'return');
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Clean');