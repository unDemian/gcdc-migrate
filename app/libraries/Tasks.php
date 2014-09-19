<?

namespace app\libraries;

use app\models\Backups;
use app\models\BackupsModel;
use app\models\MigratedDataModel;
use app\models\ServicesModel;
use app\models\SharesModel;
use app\models\TasksServicesModel;

class Tasks extends \Entity
{
	public static $name  = 'Tasks';
	public static $limit = 50;

	public static $kind  = array(
		'list'     => 'tasks#list',
		'task'     => 'tasks#task',
	);

	public static $skip = array(
		'list' => array('kind', 'id', 'selfLink'),
		'task' => array('kind', 'id', 'etag', 'selfLink')
	);

	public static $endpoints = array(
		'lists'  => 'https://www.googleapis.com/tasks/v1/users/@me/lists',
		'tasks'  => 'https://www.googleapis.com/tasks/v1/lists/%s/tasks',
	);

	public static function backup($user, $taskId = 0, $syncTaskId = 0, $ignoreUpdate = false)
	{
		// Stats
		$stats = array(
			'lists'  => 0,
			'tasks'  => 0,
		);

		// Lists
		$lists = \Rest::get(
			static::$endpoints['lists'],
			array(
				 'maxResults' => static::$limit,
			),
			$user
		);

		if( $lists && $lists['items'] ) {
			foreach($lists['items'] as $list) {

				// Save List
				$backup = BackupsModel::create();
				$backup->user_id        = $user['username']['id'];
				$backup->task_id        = $taskId;
				$backup->sync_task_id   = $syncTaskId;
				$backup->entity_id      = $list['id'];
				$backup->entity_type    = static::$kind['list'];
				$backup->entity_title   = $list['title'];
				$backup->entity_picture = \Render::image('no-photo.png');
				$backup->entity         = json_encode($list);
				$backup->created        = date(DATE_TIME);
				$backup->save();

				$stats['lists']++;

				// Save tasks
				$newTasks = array();

				do {
					$payload = array(
						'maxResults'    => static::$limit,
						'showCompleted' => 'true',
						'showHidden'    => 'true',
					);

					if(isset($tasks['nextPageToken'])) {
						$payload['pageToken'] = $tasks['nextPageToken'];
					}

					$tasks = \Rest::get(
						sprintf(static::$endpoints['tasks'], $backup->entity_id),
						$payload,
						$user
					);

					if(isset($tasks['result']['error'])) {
						d($tasks);
					}

					if(isset($tasks['items'])) {
						foreach($tasks['items'] as $task) {

							$formatedData = array(
								'user_id'        => $user['username']['id'],
								'task_id'        => $taskId,
								'sync_task_id'   => $syncTaskId,
								'parent_id'      => $backup->id,
								'entity_id'      => $task['id'],
								'entity_type'    => static::$kind['task'],
								'entity_parent'  => $backup->entity_id,
								'entity_title'   => $task['title'],
								'entity_picture' => \Render::image('no-photo.png'),
								'entity_new_id'  => '',
								'entity'         => json_encode($task),
								'created'        => date(DATE_TIME),
							);

							$newTasks[] = $formatedData;

							$stats['tasks']++;
						}
					}

				} while(isset($tasks['nextPageToken']) && $tasks['nextPageToken']);

				if($newTasks) {
					BackupsModel::insertBatch($newTasks);
				}
			}
		}

		return $stats;
	}

	public static function share($user)
	{
		// Stats
		$stats = array(
			'lists'  => array(),
		);

		// Lists
		$lists = \Rest::get(
			static::$endpoints['lists'],
			array(
				 'maxResults' => static::$limit,
			),
			$user
		);

		if( $lists && $lists['items'] ) {
			foreach($lists['items'] as $list) {

				$stats['lists'][] = array(
					'id'      => $list['id'],
					'name'    => $list['title'],
					'picture' => \Render::image('no-photo.png'),
					'data'    => $list
				);
			}
		}

		return $stats;
	}

	public static function shared($task)
	{
		$stats = array(
			'lists' => array(),
		);

		// Get contacts
		$lists = BackupsModel::all(array('sync_task_id' => $task['id'], 'user_id' => $task['user_id'], 'entity_type' => static::$kind['list']))->toArray();

		if($lists) {
			foreach($lists as $list) {
				$data = json_decode($list['entity'], true);

				$stats['lists'][] = array(
					'id'      => $list['entity_id'],
					'name'    => $list['entity_title'],
					'picture' => \Render::image('no-photo.png'),
					'data'    => $data
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
			'lists' => 0,
			'tasks' => 0,
		);

		// Get source data
		$tasks   = array();
		$lists   = BackupsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['list']))->toArray();
		$tasksDB = BackupsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['task']))->toArray();

		if($tasksDB) {
			foreach($tasksDB as $task) {
				$tasks[$task['parent_id']][] = $task;
			}
		}

		$syncedLists = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'destination_id' => $destination['username']['id'], 'kind' => static::$kind['list']))->column('identifier');
		$syncedTasks = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'destination_id' => $destination['username']['id'], 'kind' => static::$kind['task']))->column('identifier');
		$destinationLists = BackupsModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['list']))->column('entity_title');

		if($lists) {
			foreach($lists as $list) {

				// Whitelisting used for share feature
				$whiteListed = true;
				if($whitelist) {
					if(isset($whitelist['lists']) && $whitelist['lists']) {
						if( !in_array($list['entity_id'], $whitelist['lists'])) {
							$whiteListed = false;
						}
					}
				}


				if( !in_array($list['entity_id'], $syncedLists) && $whiteListed) {

					$list['entity'] = json_decode($list['entity'], true);

					$newList = array_diff_key($list['entity'], array_flip(static::$skip['list']));

					if(in_array($newList['title'], $destinationLists) && !$ignoreUpdate) {
						$newList['title'] = $newList['title'] . ' (2)';
					} else {
						$newList['title'] = $newList['title'];
					}

					$newList = \Rest::postJSON(static::$endpoints['lists'], $newList, $destination);

					if(isset($newList['result']['error'])) {
						d($newList);
					}

					$stats['lists']++;

					// Update playlist with new id
					if( !$ignoreUpdate && $newList) {
						$oldPlaylist = BackupsModel::first($list['id']);
						$oldPlaylist->entity_new_id = $newList['id'];
						$oldPlaylist->save();
					}

					$syncedCalendar = MigratedDataModel::create();
					$syncedCalendar->source_id      = $source['username']['id'];
					$syncedCalendar->destination_id = $destination['username']['id'];
					$syncedCalendar->task_id        = 0;
					$syncedCalendar->sync_task_id   = $syncTaskId;
					$syncedCalendar->table          = BackupsModel::$schema['table'];
					$syncedCalendar->table_id       = $list['id'];
					$syncedCalendar->kind           = static::$kind['list'];
					$syncedCalendar->identifier     = $list['entity_id'];
					$syncedCalendar->status         = MigratedDataModel::STATUS_ACTIVE;
					$syncedCalendar->created        = date(DATE_TIME);
					$syncedCalendar->save();

					// Add tasks
					if(isset($tasks[$list['id']])) {
						foreach($tasks[$list['id']] as $task) {

							if( !in_array($task['entity_id'], $syncedTasks)) {

								$task['entity'] = json_decode($task['entity'], true);

								$newTask = array_diff_key($task['entity'], array_flip(static::$skip['task']));
								$newTask = \Rest::postJSON(sprintf(static::$endpoints['tasks'], $newList['id']), $newTask, $destination);

								if(isset($newTask['result']['error'])) {
									d($newTask);
								}

								$stats['tasks']++;

								$syncedEvent = MigratedDataModel::create();
								$syncedEvent->source_id      = $source['username']['id'];
								$syncedEvent->destination_id = $destination['username']['id'];
								$syncedEvent->task_id        = 0;
								$syncedEvent->sync_task_id   = $syncTaskId;
								$syncedEvent->table          = BackupsModel::$schema['table'];
								$syncedEvent->table_id       = $task['id'];
								$syncedEvent->kind           = static::$kind['task'];
								$syncedEvent->identifier     = $task['entity_id'];
								$syncedEvent->status         = MigratedDataModel::STATUS_ACTIVE;
								$syncedEvent->created        = date(DATE_TIME);
								$syncedEvent->save();
							}
						}
					}
				}
			}
		}

		return $stats;
	}

	public static function _clean($destination, $source, $syncTaskId = 0, $whitelist = array())
	{
		$service = ServicesModel::first(array('library' => 'Tasks'))->toArray();

		$task = TasksServicesModel::first(array('task_id' => $syncTaskId, 'service_id' => $service['id']))->toArray();
		$task['stats'] = json_decode($task['stats'], true);

		// lists
		$lists = BackupsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['list']))->toArray();
		if($lists && ($task['stats']['lists'] || $destination['username']['id'] == $source['username']['id'])) {
			foreach($lists as $list) {

				// $whitelist - shared or clean data
				if($whitelist) {
					if( isset($whitelist['lists']) && in_array($list['entity_id'], $whitelist['lists']) ) {
						\Rest::delete('https://www.googleapis.com/tasks/v1/users/@me/lists/' . $list['entity_id'], array(), $destination);
					}
				} else {
					if($destination['username']['id'] == $source['username']['id']) {
						\Rest::delete('https://www.googleapis.com/tasks/v1/users/@me/lists/' . $list['entity_id'], array(), $destination);
					} else {
						if( $list['entity_new_id']) {
							\Rest::delete('https://www.googleapis.com/tasks/v1/users/@me/lists/' . $list['entity_new_id'], array(), $destination);
						}
					}
				}
			}
		}
	}

	public static function _cleanDB($syncTaskId)
	{
		// Clear DB data
		MigratedDataModel::softDelete(array('sync_task_id' => $syncTaskId));
	}
}