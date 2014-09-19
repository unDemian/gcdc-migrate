<?
	namespace app\models\Calendar;

	use app\libraries\Calendar;

	class EventsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_calendar_events',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'kind', 'calendar_id', 'google_calendar_id', 'event_id', 'name', 'description', 'location', 'event_status', 'url', 'color_id', 'created_at', 'creator', 'start', 'end', 'recurrence', 'status' )
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

		public static function identifiers($params)
		{
			$results = array();

			$query = '
			SELECT
				z_calendar_events.*,
				z_calendar.timezone
			FROM
				z_calendar_events
				LEFT JOIN z_calendar
					   ON z_calendar.id = z_calendar_events.calendar_id
			WHERE
				z_calendar_events.user_id = "' . $params['user_id'] . '"
				AND z_calendar_events.sync_task_id = "' . $params['sync_task_id'] . '"
			';

			$events = static::execute($query);
			if($events) {
				$events = $events->toArray();
				if($events) {
					foreach($events as $event) {
						$results[] = Calendar::identifier($event, $event);
					}
				}
			}

			return $results;
		}

	}