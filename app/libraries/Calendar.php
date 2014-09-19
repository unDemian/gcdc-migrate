<?

namespace app\libraries;

use app\models\Calendar\CalendarsModel;
use app\models\Calendar\EventsModel;
use app\models\SharesModel;
use app\models\ServicesModel;
use app\models\TasksServicesModel;
use app\models\MigratedDataModel;

class Calendar extends \Entity
{

	public static $name              = 'Calendar';
	public static $limit             = 50;

	public static $kind = array(
		'calendar'     => 'calendar#calendar',
		'event'        => 'calendar#event',
	);

	public static $endpoints = array(
		'calendars' => 'https://www.googleapis.com/calendar/v3/users/me/calendarList',
		'calendar'  => 'https://www.googleapis.com/calendar/v3/calendars',
		'events'    => 'https://www.googleapis.com/calendar/v3/calendars/%s/events',
	);

	public static function backup($user, $taskId = 0, $syncTaskId = 0, $ignoreUpdate = false)
	{
		// Stats
		$stats = array(
			'calendars' => 0,
			'events'    => 0,
		);

		// Calendars
		$calendars = \Rest::get(
			static::$endpoints['calendars'],
			array(
				 'maxResults' => static::$limit,
				 'showHidden' => 'true',
			),
			$user
		);

		if( $calendars && $calendars['items'] ) {
			foreach($calendars['items'] as $calendar) {

				// Save Calendar
				$newCalendar = CalendarsModel::create();
				$newCalendar->user_id      = $user['username']['id'];
				$newCalendar->task_id      = $taskId;
				$newCalendar->sync_task_id = $syncTaskId;
				$newCalendar->kind         = static::$kind['calendar'];
				$newCalendar->calendar_id  = $calendar['id'];
				$newCalendar->name         = $calendar['summary'];
				$newCalendar->timezone     = $calendar['timeZone'];
				$newCalendar->color_id     = $calendar['colorId'];
				$newCalendar->background   = $calendar['backgroundColor'];
				$newCalendar->foreground   = $calendar['foregroundColor'];
				$newCalendar->selected     = isset($calendar['selected']) ? $calendar['selected'] : '' ;
				$newCalendar->status       = CalendarsModel::STATUS_ACTIVE;
				$newCalendar->save();

				$stats['calendars']++;

				// Save events
				$newEvents = array();
				do {
					$payload = array(
						'maxResults' => static::$limit,
					);

					if(isset($events['nextPageToken'])) {
						$payload['pageToken'] = $events['nextPageToken'];
					}

					$events = \Rest::get(
						sprintf(static::$endpoints['events'], $newCalendar->calendar_id),
						$payload,
						$user
					);

					if(isset($events['result']['error'])) {
						d($events);
					}

					if(isset($events['items'])) {
						foreach($events['items'] as $event) {

							if($event['status'] != 'cancelled') {
								$formatedData = array(
									'user_id'            => $user['username']['id'],
									'task_id'            => $taskId,
									'sync_task_id'       => $syncTaskId,
									'kind'               => static::$kind['event'],
									'calendar_id'        => $newCalendar->id,
									'google_calendar_id' => $newCalendar->calendar_id,
									'event_id'           => $event['id'],
									'name'               => (isset($event['summary']) ? $event['summary'] : ''),
									'description'        => (isset($event['description']) ? $event['description'] : ''),
									'location'           => (isset($event['location']) ? $event['location'] : ''),
									'event_status'       => $event['status'],
									'url'                => isset($event['htmlLink']) ? $event['htmlLink'] : '',
									'color_id'           => isset($event['colorId']) ? $event['colorId'] : '',
									'created_at'         => date(DATE_TIME, strtotime($event['created'])),
									'creator'            => json_encode($event['creator']),
									'start'              => json_encode($event['start']),
									'end'                => json_encode($event['end']),
									'recurrence'         => (isset($event['recurrence'])) ? json_encode($event['recurrence']) : '',
									'status'             => EventsModel::STATUS_ACTIVE
								);

								$newEvents[] = $formatedData;

								$stats['events']++;
							}
						}
					}

				} while(isset($events['nextPageToken']) && $events['nextPageToken']);

				if($newEvents) {
					EventsModel::insertBatch($newEvents);
				}
			}
		}

		return $stats;
	}

	public static function share($user)
	{
		// Stats
		$stats = array(
			'calendars'     => array(),
		);

		// Calendars
		$calendars = \Rest::get(
			static::$endpoints['calendars'],
			array(
				 'maxResults' => static::$limit,
				 'showHidden' => 'true',
			),
			$user
		);

		if( $calendars && $calendars['items'] ) {
			foreach($calendars['items'] as $calendar) {
				$stats['calendars'][] = array(
					'id'      => $calendar['id'],
					'name'    => $calendar['summary'],
					'picture' => \Render::image('no-photo.png'),
					'data'    => $calendar
				);
			}
		}

		return $stats;
	}

	public static function shared($task)
	{
		$stats = array(
			'calendars' => array(),
		);

		// Get calendars
		$calendars = CalendarsModel::withEvents($task['user_id'], $task['id']);
		if($calendars) {
			foreach($calendars as $calendar) {
				$stats['calendars'][] = array(
					'id'      => $calendar['calendar_id'],
					'name'    => $calendar['name'],
					'picture' => \Render::image('no-photo.png'),
					'data'    => $calendar
				);
			}
		}

		return $stats;
	}

	public static function clean($source, $destination, $syncTaskId = 0, $ignoreUpdate = false)
	{
		$share = SharesModel::first(array('task_id' => $syncTaskId));

		if($share) {
			$share = $share->toArray();
			$data  = json_decode($share['data'], true);

			static::_clean($source, $source, $syncTaskId, $data);
		}
	}

	public static function _transfer($source, $destination, $syncTaskId = 0, $ignoreUpdate = false, $whitelist = array())
	{
		// Stats
		$stats = array(
			'calendars' => 0,
			'events'    => 0,
		);

		// Get source data
		$calendars            = CalendarsModel::withEvents($source['username']['id'], $syncTaskId);
		$destinationCalendars = CalendarsModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId))->toArray();
		$destinationNames     = static::_getColumn($destinationCalendars, 'name');

		$syncedEvents         = EventsModel::identifiers(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId));

		if($calendars) {
			foreach($calendars as $calendar) {

				// Create calendar
				$payload = array();

				// Whitelisting used for share feature
				$whiteListed = true;
				if($whitelist) {
					if(isset($whitelist['calendars']) && $whitelist['calendars']) {
						if( !in_array($calendar['calendar_id'], $whitelist['calendars'])) {
							$whiteListed = false;
						}
					}
				}

				// New
				if( (!in_array($calendar['name'], array_keys($destinationNames)) && $whiteListed) || ($whiteListed && $ignoreUpdate)  ) {

					// Create Calendar
					$payload['summary'] = $calendar['name'];
					$newCalendar = \Rest::postJSON(static::$endpoints['calendar'], $payload, $destination);

					$stats['calendars']++;

					if(isset($newCalendar['result']['error'])) {
						d($newCalendar);
					}

					// Create Calendar List
					$payloadList = array();
					$payloadList['id' ]             = $newCalendar['id'];
					$payloadList['timezone']        = $calendar['timezone'];
					$payloadList['colorId']         = $calendar['color_id'];
					$payloadList['backgroundColor'] = $calendar['background'];
					$payloadList['foregroundColor'] = $calendar['foreground'];
					$payloadList['selected']        = true;
					\Rest::postJSON(static::$endpoints['calendars'], $payloadList, $destination);

					$syncedCalendar = MigratedDataModel::create();
					$syncedCalendar->source_id      = $source['username']['id'];
					$syncedCalendar->destination_id = $destination['username']['id'];
					$syncedCalendar->task_id        = 0;
					$syncedCalendar->sync_task_id   = $syncTaskId;
					$syncedCalendar->table          = CalendarsModel::$schema['table'];
					$syncedCalendar->table_id       = $calendar['id'];
					$syncedCalendar->kind           = static::$kind['calendar'];
					$syncedCalendar->identifier     = $calendar['calendar_id'];
					$syncedCalendar->name           = $calendar['name'];
					$syncedCalendar->status         = MigratedDataModel::STATUS_ACTIVE;
					$syncedCalendar->created        = date(DATE_TIME);
					$syncedCalendar->save();

					// Update calendar with new id
					if( !$ignoreUpdate ) {
						$oldCalendar = CalendarsModel::first($calendar['id']);
						$oldCalendar->calendar_new_id = $newCalendar['id'];
						$oldCalendar->save();
					}

				// Existing
				} else {
					$cal = CalendarsModel::first(array('user_id' => $destination['username']['id'], 'name' => $calendar['name']))->toArray();
					$newCalendar['id'] = $cal['calendar_id'];
				}

				// Add events
				if($calendar['events']) {
					foreach($calendar['events'] as $event) {

						$combination = static::identifier($event, $calendar);

						// Whitelisting used for share feature
						$whiteListed = true;
						if($whitelist) {
							if(isset($whitelist['calendars']) && $whitelist['calendars']) {
								if( !in_array($event['google_calendar_id'], $whitelist['calendars'])) {
									$whiteListed = false;
								}
							}
						}

						if( (!in_array($combination, $syncedEvents) && $whiteListed) || ($whiteListed && $ignoreUpdate) ) {

							// Create Event
							$payload                = array();
							$payload['summary']     = $event['name'];
							$payload['description'] = $event['description'];
							$payload['location']    = $event['location'];
							$payload['status']      = $event['event_status'];
							if($event['color_id']) {
								$payload['colorId']     = $event['color_id'];
							}
							$payload['creator']     = json_decode($event['creator'], true);
							$payload['start']       = json_decode($event['start'], true);
							$payload['end']         = json_decode($event['end'], true);
							if(json_decode($event['recurrence'], true)) {
								$payload['recurrence']  = json_decode($event['recurrence'], true);
							}
							$newEvent = \Rest::postJSON(sprintf(static::$endpoints['events'], $newCalendar['id']), $payload, $destination);

							if(isset($newEvent['result']['error'])) {
								d($newEvent);
							}

							$stats['events']++;

							$syncedEvent = MigratedDataModel::create();
							$syncedEvent->source_id      = $source['username']['id'];
							$syncedEvent->destination_id = $destination['username']['id'];
							$syncedEvent->task_id        = 0;
							$syncedEvent->sync_task_id   = $syncTaskId;
							$syncedEvent->table          = EventsModel::$schema['table'];
							$syncedEvent->table_id       = $event['id'];
							$syncedEvent->kind           = static::$kind['event'];
							$syncedEvent->identifier     = $event['event_id'];
							$syncedEvent->unique         = $combination;
							$syncedEvent->name           = $event['name'];
							$syncedEvent->created        = date(DATE_TIME);
							$syncedEvent->status         = MigratedDataModel::STATUS_ACTIVE;
							$syncedEvent->save();
						}
					}
				}
			}
		}

		return $stats;
	}

	public static function _clean($destination, $source, $syncTaskId = 0, $whitelist = array())
	{
		$service = ServicesModel::first(array('library' => 'Calendar'))->toArray();

		$task = TasksServicesModel::first(array('task_id' => $syncTaskId, 'service_id' => $service['id']))->toArray();
		$task['stats'] = json_decode($task['stats'], true);

		// Calendars
		$calendars = CalendarsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId))->toArray();

		// Events
		$toDeleteEvents = array();
		$migratedEvents = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'kind' => static::$kind['event']));
		if($migratedEvents) {
			$migratedEvents = $migratedEvents->toArray();

			if($migratedEvents) {
				$destinationCalendars = CalendarsModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId));

				if($destinationCalendars) {
					$destinationCalendars = $destinationCalendars->toArray();

					if($destinationCalendars) {

						foreach($destinationCalendars as $calendar) {

							// get events
							$newEvents = array();
							do {
								$payload = array('maxResults' => static::$limit);

								if(isset($events['nextPageToken'])) {
									$payload['pageToken'] = $events['nextPageToken'];
								}

								$events = \Rest::get(
									sprintf(static::$endpoints['events'], $calendar['calendar_id']),
									$payload,
									$destination
								);

								if(isset($events['result']['error'])) {
									d(static::$endpoints['events']);
								}

								if(isset($events['items'])) {
									foreach($events['items'] as $event) {
										if(isset($event['summary'])) {
											$newEvents[static::identifier($event, $calendar)] = $event['id'];
										}
									}
								}
							} while(isset($events['nextPageToken']) && $events['nextPageToken']);

							// Mark events
							foreach($migratedEvents as $event) {
								if(isset($newEvents[$event['unique']])) {
									$toDeleteEvents[] = array(
										'eventId'    => $newEvents[$event['unique']],
										'calendarId' => $calendar['calendar_id'],
									);
								}
							}
						}
					}
				}
			}
		}

		switch(true) {

			# Share / Clean
			############################################################################
			case ( !empty($whitelist) ):

				// Calendars
				if( $calendars) {
					foreach($calendars as $calendar) {
						if( isset($whitelist['calendars']) && in_array($calendar['calendar_id'], $whitelist['calendars']) ) {
							\Rest::delete('https://www.googleapis.com/calendar/v3/calendars/' . $calendar['calendar_id'], array(), $destination);
						}
					}
				}

				break;

			# Move
			############################################################################
			case ( $destination['username']['id'] == $source['username']['id'] ):

				// Calendars
				if( $calendars ) {
					foreach($calendars as $calendar) {
						\Rest::delete('https://www.googleapis.com/calendar/v3/calendars/' . $calendar['calendar_id'], array(), $destination);
					}
				}

			# Migrate / Sync
			############################################################################
			default:

				// Calendars
				if( $calendars && $task['stats']['calendars'] ) {
					foreach($calendars as $calendar) {
						if( $calendar['calendar_new_id']) {
							\Rest::delete('https://www.googleapis.com/calendar/v3/calendars/' . $calendar['calendar_new_id'], array(), $destination);
						}
					}
				}

				// Events
				if( $toDeleteEvents && $task['stats']['events'] ) {
					foreach($toDeleteEvents as $event) {
						\Rest::delete('https://www.googleapis.com/calendar/v3/calendars/' . $event['calendarId'] . '/events/' . $event['eventId'], array(), $destination);
					}
				}
				break;
		}
	}

	public static function _cleanDB($syncTaskId)
	{
		// Clear DB data
		MigratedDataModel::softDelete(array('sync_task_id' => $syncTaskId));
	}

	public static function identifier($event = array(), $calendar = array())
	{
		// TIMEZONENING
		/////////////////////////////////////////////////////////////////
		// Start
		if(isset($event['start']) && $calendar['timezone']) {
			if( !is_array($event['start']) ) {
				$start = json_decode($event['start'], true);
			} else {
				$start = $event['start'];
			}


			if($start) {
				if(isset($start['dateTime'])) {
					$startDate = new \DateTime($start['dateTime'], new \DateTimeZone($calendar['timezone']));
					date_default_timezone_set('UTC');
					$startDate = date("c", $startDate->format('U'));
				}
			}
		}

		// End
		if(isset($event['end']) && $calendar['timezone']) {
			if( !is_array($event['end']) ) {
				$end = json_decode($event['end'], true);
			} else {
				$end = $event['end'];
			}
			if($end) {
				if(isset($end['dateTime'])) {
					$endDate = new \DateTime($end['dateTime'], new \DateTimeZone($calendar['timezone']));
					date_default_timezone_set('UTC');
					$endDate = date("c", $endDate->format('U'));
				}
			}
		}

		$combination = isset($event['summary']) ? $event['summary'] : $event['name'];
		if(isset($startDate)) {
			$combination .= $startDate;
		}
		if(isset($endDate)) {
			$combination .= $endDate;
		}

		return md5($combination);
	}

}