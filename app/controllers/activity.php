<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	use app\libraries\Tasks;
	use app\models\TasksModel;

	/**
	 * Handle accounts pages
	 *
	 * @package app\controllers
	 */
	class Activity extends \Controller
	{
		/**
		 * Listing accounts
		 */
		public static function index()
		{
			$templateData = array(
				'template' => 'activity/content',
				'title'    => 'Migrate - Activity Queue',
				'bodyId'   => 'tasks',
				'styles'   => array( 'activity.css' ),
				'scripts'  => array( 'activity.js' ),

				// Specific
				'polling'     => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id'], 'status' => array(TasksModel::STATUS_PROGRESS, TasksModel::STATUS_REVERTING))) ? 'yes' : 'no',
				'tasks'       => TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id'])),
			);

			// Mark as viewed
			if($templateData['polling'] == 'no') {
				TasksModel::markAsRead(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id']));
			}

			\Render::layout('template', $templateData);
		}

		public static function feed()
		{
			$tasks = TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id']));
			if($tasks) {
				$output = \Render::view('activity/table', compact('tasks'), 'return');
			} else {
				$output = '
				<div class="alert alert-warning text-center alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					Your history is clean, you did not use this feature before.
				</div>';
			}

			// Mark as viewed
			TasksModel::markAsRead(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id']));

			echo $output;
		}

		public static function notifications()
		{
			$count = 0;
			if(isset($_SESSION['current'])) {
				$count = count(TasksModel::listingFor(array('user_id' => $_SESSION['current']['username']['id'], 'user_affected_id' => $_SESSION['current']['username']['id'], 'status' => array(TasksModel::STATUS_FINISHED, TasksModel::STATUS_REVERTED), 'viewed' => '0')));
			}
			echo $count;
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Activity');


