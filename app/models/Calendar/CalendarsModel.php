<?
	namespace app\models\Calendar;

	use app\models\Calendar\EventsModel;

	class CalendarsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_calendar',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'kind', 'calendar_id', 'calendar_new_id', 'name', 'timezone', 'color_id', 'background', 'foreground', 'selected', 'status' )
		);

		const STATUS_DELETED = 0;
		const STATUS_ACTIVE  = 1;

		/**
		 * Required in every model. Please do not edit!
		 *
		 * @param array $params
		 */
		public function __construct($params = array())
		{
			if($params) {
				parent::__construct($params);
			} elseif(isset($this->settings)) {
				unset($this->settings);
			}
		}

		public static function withEvents($userId, $taskId)
		{
			$calendars = static::all(array('user_id' => $userId, 'sync_task_id' => $taskId))->toArray();

			if($calendars) {
				foreach($calendars as $key => $calendar) {
					$calendars[$key]['events'] = EventsModel::all(array('calendar_id' => $calendar['id']))->toArray();
				}
			}

			return $calendars;
		}
	}