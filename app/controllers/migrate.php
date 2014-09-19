<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	// Appengine
	require_once 'google/appengine/api/taskqueue/PushTask.php';

	// Controllers & Models
	use app\libraries\Calendar;
	use app\libraries\Tasks;
	use app\libraries\Contacts;
	use app\libraries\Youtube;
	use app\models\IntroModel;
	use app\models\BackupsModel;
	use app\models\Calendar\CalendarsModel;
	use app\models\MigratedDataModel;
	use app\models\ServicesActionsModel;
	use app\models\ServicesModel;
	use app\models\TasksModel;
	use app\models\TasksServicesModel;
	use app\models\Youtube\PlaylistsModel;
	use app\models\Youtube\SubscriptionsModel;
	use \google\appengine\api\taskqueue\PushTask;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Migrate extends \Controller
	{
		/**
		 * Listing accounts
		 */
		public static function index($source = -1, $destination = -1)
		{
			\Auth::showWizard('finish');

			// Wizard Init
			if( ! isset($_SESSION['wizard'])) {
				$_SESSION['wizard']['action'] = 0;
				$_SESSION['wizard']['services'] = array();
			}

			static::_setUsers($source, $destination);

			$source      = static::_getUser('source');
			$destination = static::_getUser('destination');

			$_SESSION['wizard']['commonServices'] = (isset($source['services']) && isset($destination['services'])) ? @array_intersect_assoc($source['services'], $destination['services']) : array();

			$templateData = array(
				'template'    => 'migrate/content',
 				'title'       => 'Migrate - Migrate',
				'bodyId'      => 'migrate',
				'styles'      => array(
					'common/wizard.css',
					'migrate/migrate.css',
				),
				'scripts'     => array(
					'common/wizard.js',
					'migrate/migrate.js',
				),

				'source'      => $source,
				'destination' => $destination,
				'services'    => $_SESSION['wizard']['commonServices'],
				'polling'     => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id'], 'type' => array(TasksModel::TYPE_SYNC, TasksModel::TYPE_MIGRATE, TasksModel::TYPE_MOVE), 'status' => array(TasksModel::STATUS_SCHEDULED, TasksModel::STATUS_PROGRESS, TasksModel::STATUS_REVERTING))) ? 'yes' : 'no',
				'tasks'       => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id'], 'type' => array(TasksModel::TYPE_SYNC, TasksModel::TYPE_MIGRATE, TasksModel::TYPE_MOVE))),
				'disabled'    => count($_SESSION['usernames']) > 1 ? '' : 'disabled',
				'intro'       => (bool) IntroModel::first(array('page' => 'migrate', 'group' => $_SESSION['current']['username']['group']))
			);

			if( !$templateData['intro'] ) {
				IntroModel::create(array('page' => 'migrate', 'group' => $_SESSION['current']['username']['group']))->save();
			}

			if($templateData['disabled']) {
				\Util::notice(array('type' => 'warning', 'text' => 'This operation requires at least two accounts. Click <a href="' . \Render::link('accounts/add') . '">here</a> to add another account.', 'persistent' => true));
			}

			\Render::layout('template', $templateData);
		}

		public static function details($id)
		{
			$templateData = array(
				'template'    => 'migrate/details',
				'title'       => 'Migrate - Details',
				'bodyId'      => 'details',
				'styles'      => array(
					'common/wizard.css',
					'migrate/details.css'
				),
				'scripts'     => array(
					'common/wizard.js',
					'migrate/details.js',
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

			$kinds = array();
			$graphData = array();
			switch($_service['name']) {
				case 'Youtube':
					$kinds = array(Youtube::$kind['playlist'], Youtube::$kind['subscription']);

					// Graph data
					$graphData['source']['playlists'] = PlaylistsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_id']))->toArray();
					$graphData['source']['subscriptions'] = SubscriptionsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_id']))->toArray();

					$graphData['destination']['playlists'] = PlaylistsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_affected_id']))->toArray();
					$graphData['destination']['subscriptions'] = SubscriptionsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_affected_id']))->toArray();

					break;

				case 'Contacts':
					$kinds = array(Contacts::$kind['contact']);

					// Graph data
					$graphData['source']['contacts']      = BackupsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_id'], 'entity_type' => Contacts::$kind['contact']))->toArray();
					$graphData['destination']['contacts'] = BackupsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_affected_id'], 'entity_type' => Contacts::$kind['contact']))->toArray();

					break;

				case 'Tasks':
					$kinds = array(Tasks::$kind['list']);

					// Graph data
					$graphData['source']['lists']      = BackupsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_id'], 'entity_type' => Tasks::$kind['list']))->toArray();
					$graphData['destination']['lists'] = BackupsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_affected_id'], 'entity_type' => Tasks::$kind['list']))->toArray();

					break;

				case 'Calendar':
					$kinds = array(Calendar::$kind['calendar']);

					// Graph data
					$graphData['source']['calendars']      = BackupsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_id'], 'entity_type' => Calendar::$kind['calendar']))->toArray();
					$graphData['destination']['calendars'] = BackupsModel::all(array('sync_task_id' => $taskId, 'user_id' => $task['user_affected_id'], 'entity_type' => Calendar::$kind['calendar']))->toArray();

					break;
			}

			// Migrated Data
			$migratedData = array();
			$migrated = MigratedDataModel::all(array('sync_task_id' => $taskId, 'kind' => $kinds))->toArray();
			if($migrated) {
				foreach($migrated as $migrate) {
					switch($migrate['kind']) {
						case Youtube::$kind['playlist']:
							$play = PlaylistsModel::first($migrate['table_id'])->toArray();
							$migratedData['playlists'][] = $play;
							$migratedData['playlistsGraph'][$migrate['source_id']][] = $play;
							$migratedData['playlistsIds'][] = $play['id'];
							break;

						case Youtube::$kind['subscription']:
							$subs = SubscriptionsModel::first($migrate['table_id'])->toArray();
							$migratedData['subscriptions'][] = $subs;
							$migratedData['subscriptionsGraph'][$migrate['source_id']][] = $subs;
							$migratedData['subscriptionsIds'][] = $subs['id'];
							break;

						case Contacts::$kind['contact']:
							$contact = BackupsModel::first($migrate['table_id'])->toArray();
							$migratedData['contacts'][] = $contact;
							$migratedData['contactsGraph'][$migrate['source_id']][] = $contact;
							$migratedData['contactsIds'][] = $contact['id'];
							break;

						case Tasks::$kind['list']:
							$contact = BackupsModel::first($migrate['table_id'])->toArray();
							$migratedData['lists'][] = $contact;
							$migratedData['listsGraph'][$migrate['source_id']][] = $contact;
							$migratedData['listsIds'][] = $contact['id'];
							break;

						case Calendar::$kind['calendar']:
							$calendar = CalendarsModel::first($migrate['table_id'])->toArray();
							$migratedData['calendars'][] = $calendar;
							$migratedData['calendarsGraph'][$migrate['source_id']][] = $calendar;
							$migratedData['calendarsIds'][] = $calendar['id'];
							break;
					}
				}
			}

			echo \Render::view('migrate/details/' . strtolower($_service['name']), array('task' => $task, 'service' => $_service, 'migratedData' => $migratedData, 'graphData' => $graphData), 'return');
		}

		public static function feed()
		{
			$tasks = TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id'], 'type' => array(TasksModel::TYPE_SYNC, TasksModel::TYPE_MIGRATE, TasksModel::TYPE_MOVE)));
			if($tasks) {
				$output = \Render::view('migrate/table', compact('tasks'), 'return');
			} else {
				$output = \Render::view('common/empty', false, 'return');
			}

			echo $output;
		}

		public static function start()
		{
			// Create task
			$task = TasksModel::create();

			$task->user_id          = $_SESSION['wizard']['source']['username']['id'];
			$task->user_affected_id = $_SESSION['wizard']['destination']['username']['id'];
			$task->group_id         = $_SESSION['current']['username']['group'];
			$task->type             = \Util::action(false, $_SESSION['wizard']['action']);
			$task->title            = ucfirst(\Util::action()) . ' Data';
			$task->created_at       = date(DATE_TIME);
			$task->estimate         = 10 * 60;
			$task->save();

			// Add services
			if($_SESSION['wizard']['services']) {
				foreach($_SESSION['wizard']['services'] as $serviceId => $service) {
					$serviceId = (int) $serviceId;
					TasksServicesModel::create(array('task_id' => $task->id, 'service_id' => $serviceId))->save();
				}

				$task->contains = json_encode(ServicesActionsModel::getActions($_POST['services']));
				$task->save();
			}

			// Reset wizard
			unset($_SESSION['wizard']);

			$task = new PushTask('/queue/add/' . $task->id);
			$task->add();

			// Notification
			\Util::notice(array('type' => 'success', 'text' => 'Your ' . \Util::action('termination') . ' process has started. Check the list below to see it\'s status.'));
		}

		public static function revert($taskId)
		{
			$task = new PushTask('/queue/add/' . $taskId . '/revert');
			$task->add();

			// Notification
			\Util::notice(array('type' => 'success', 'text' => 'Your ' . \Util::action('termination') . ' process is now beeing reverted. Check the list below to see it\'s status.'));
			\Router::redirect('migrate');
		}

		// Users
		public static function _setUsers($source = -1, $destination = -1)
		{
			// First access
			if(
				!isset($_SESSION['wizard']['source']) || is_null($_SESSION['wizard']['source']) || !$_SESSION['wizard']['source']  ||
				!isset($_SESSION['wizard']['destination']) || is_null($_SESSION['wizard']['destination']) || !$_SESSION['wizard']['destination']) {

				$_SESSION['wizard']['source']      = $_SESSION['current'];
				$_SESSION['wizard']['destination'] = \Auth::userBeside($_SESSION['current']['username']['id']);
			}

			// Selected source and destination
			if($source !== -1 && $destination !== -1) {
				if($source == $destination) {

					if($_SESSION['wizard']['source']['username']['id'] == $source) {
						$_SESSION['wizard']['source']      = \Auth::userBeside($source);
						$_SESSION['wizard']['destination'] = $_SESSION['usernames'][$destination];
					} else {
						$_SESSION['wizard']['source']      = $_SESSION['usernames'][$source];
						$_SESSION['wizard']['destination'] = \Auth::userBeside($source);
					}

				} else {
					$_SESSION['wizard']['source']      = $_SESSION['usernames'][$source];
					$_SESSION['wizard']['destination'] = $_SESSION['usernames'][$destination];
				}

				$_SESSION['current'] = $_SESSION['wizard']['source'];

				\Router::redirect('migrate');
			}
		}

		public static function _getUser($output = 'source')
		{
			return $_SESSION['wizard'][$output];
		}

		// Wizard
		public static function action()
		{
			$_SESSION['wizard']['action'] = (int) $_POST['id'];
		}

		public static function users()
		{
			$_SESSION['wizard']['source']      = $_SESSION['usernames'][(int)$_POST['sourceId']];
			$_SESSION['wizard']['destination'] = $_SESSION['usernames'][(int) $_POST['destinationId']];

			$_SESSION['wizard']['services'] = array();

			$_SESSION['wizard']['commonServices'] = (isset($_SESSION['wizard']['source']['services']) && isset($_SESSION['wizard']['destination']['services'])) ? @array_intersect_assoc($_SESSION['wizard']['source']['services'], $_SESSION['wizard']['destination']['services']) : array();

			echo json_encode(array('count' => count($_SESSION['wizard']['commonServices'])));
		}

		public static function services()
		{
			echo \Render::view('migrate/services', array('services' => $_SESSION['wizard']['commonServices']), 'return');
		}

		public static function selectService()
		{
			$id = (int) $_POST['id'];

			if($_POST['action'] == 'select') {
				$_SESSION['wizard']['services'][$id] = ServicesModel::first($id)->toArray();
			} else {
				if(isset($_SESSION['wizard']['services'][$id])) {
					unset($_SESSION['wizard']['services'][$id]);
				}
			}
		}

		public static function finish()
		{
			echo \Render::view('migrate/finish', array('services' => $_SESSION['wizard']['services']), 'return');
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Migrate');