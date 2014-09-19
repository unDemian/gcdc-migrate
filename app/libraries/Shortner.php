<?

namespace app\libraries;

use app\models\Backups;
use app\models\BackupsModel;
use app\models\MigratedDataModel;

class Shortner
{
	public static $name  = 'Shortner';
	public static $limit = 50;

	public static $kind  = array(
		'link'     => 'shortner#link',
	);

	public static $skip = array(
		'link' => array('kind', 'id', 'selfLink'),
	);

	public static $endpoints = array(
		'links'  => 'https://www.googleapis.com/urlshortener/v1/url/history',
		'link'  => 'https://www.googleapis.com/urlshortener/v1/url',
	);

	public static function sync($source, $destination, $syncTaskId = 0)
	{
		// Stats
		$stats = array(
			'links' => 0,
		);

		// Get source data
		$links       = BackupsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'entity_type' => static::$kind['link']))->toArray();

		$syncedLinks = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'destination_id' => $destination['username']['id'], 'kind' => static::$kind['link']))->column('identifier');

		if($links) {
			foreach($links as $link) {

				if( !in_array($link['entity_id'], $syncedLinks) ) {

					$link['entity'] = json_decode($link['entity'], true);

					$newLink = array_diff_key($link['entity'], array_flip(static::$skip['link']));
					$newLink = \Rest::postJSON(static::$endpoints['link'], $newLink, $destination);

					if(isset($newLink['result']['error'])) {
						d($newLink);
					}

					$stats['links']++;

					$syncedCalendar = MigratedDataModel::create();
					$syncedCalendar->source_id      = $source['username']['id'];
					$syncedCalendar->destination_id = $destination['username']['id'];
					$syncedCalendar->kind           = static::$kind['link'];
					$syncedCalendar->identifier     = $link['entity_id'];
					$syncedCalendar->created        = date(DATE_TIME);
					$syncedCalendar->save();
				}
			}
		}

		return $stats;
	}

	public static function backup($user, $taskId = 0, $syncTaskId = 0)
	{
		// Stats
		$stats = array(
			'links'  => 0,
		);

		// Lists
		$lists = \Rest::get(
			static::$endpoints['links'],
			array(),
			$user
		);

		if( $lists && $lists['items'] ) {
			foreach($lists['items'] as $list) {

				// Save List
				$backup = BackupsModel::create();
				$backup->user_id      = $user['username']['id'];
				$backup->task_id      = $taskId;
				$backup->sync_task_id = $syncTaskId;
				$backup->entity_id    = $list['id'];
				$backup->entity_type  = static::$kind['link'];
				$backup->entity       = json_encode($list);
				$backup->created      = date(DATE_TIME);
				$backup->save();

				$stats['links']++;
			}
		}

		return $stats;
	}

	public static function clean($destination,$die = false)
	{

	}

	public static function export()
	{

	}
}