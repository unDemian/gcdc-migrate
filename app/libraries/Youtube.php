<?

namespace app\libraries;

use app\models\ServicesModel;
use app\models\SharesModel;
use app\models\TasksServicesModel;
use app\models\Youtube\ChannelsModel;
use app\models\Youtube\PlaylistsModel;
use app\models\Youtube\SubscriptionsModel;
use app\models\Youtube\VideosModel;
use app\models\MigratedDataModel;

class Youtube extends \Entity
{
	public static $name              = 'Youtube';
	public static $excludedPlaylists = array( 'History' );
	public static $limit             = 50;

	public static $kind = array(
		'playlist'     => 'youtube#playlist',
		'video'        => 'youtube#video',
		'channel'      => 'youtube#channel',
		'subscription' => 'youtube#subscription',
	);

	public static $endpoints = array(
		'channels'      => 'https://www.googleapis.com/youtube/v3/channels',
		'playlists'     => 'https://www.googleapis.com/youtube/v3/playlists',
		'playlistItems' => 'https://www.googleapis.com/youtube/v3/playlistItems',
		'subscriptions' => 'https://www.googleapis.com/youtube/v3/subscriptions',
	);

	public static $part = array(
		'channels' => array(
			'minimal' => 'id,snippet,contentDetails',
			'all'     => 'id,snippet,auditDetails,brandingSettings,contentDetails,invideoPromotion,statistics,topicDetails'
		),
		'playlists' => array(
			'minimal' => 'id,snippet,status',
			'all'     => 'id,snippet,status,contentDetails,player',
		),
		'playlistItems' => array(
			'minimal' => 'id,snippet,contentDetails,status',
		),
		'subscriptions' => array(
			'minimal' => 'id,snippet,contentDetails',
		),
	);

	public static function backup($user, $taskId = 0, $syncTaskId = 0, $ignoreUpdate = false)
	{
		// Stats
		$stats = array(
			'playlists'     => 0,
			'videos'        => 0,
			'subscriptions' => 0,
		);

		// Channels
		$channels = \Rest::get(
			static::$endpoints['channels'],
			array(
				 'part'       => static::$part['channels']['minimal'],
				 'maxResults' => static::$limit,
				 'mine'       => 'true',
			)
			, $user
		);

		$dbChannels = array();
		$playlists = array();

		if( $channels) {
			if($channels['pageInfo']['totalResults'] < static::$limit) {
				foreach($channels['items'] as $channel) {

					// Save channel
					$formatedData = array(
						'user_id'       => $user['username']['id'],
						'task_id'       => $taskId,
						'sync_task_id'  => $syncTaskId,
						'etag'          => $channel['etag'],
						'kind'          => static::$kind['playlist'],
						'channel_id'    => $channel['id'],
						'title'         => $channel['snippet']['title'],
						'description'   => $channel['snippet']['description'],
						'created_at'    => date(DATE_TIME, strtotime($channel['snippet']['publishedAt'])),
						'picture'       => $channel['snippet']['thumbnails']['default']['url'],
						'status'        => ChannelsModel::STATUS_ACTIVE
					);

					$dbChannels[] = ChannelsModel::create($formatedData)->save();

					if($channel['contentDetails']) {
						if($channel['contentDetails']['relatedPlaylists']) {
							foreach($channel['contentDetails']['relatedPlaylists'] as $playlistId)
							$playlists[] = $playlistId;
						}
					}
				}
			}
		}

		// Get channel playlists
		$channelPlaylists = \Rest::get(
			static::$endpoints['playlists'],
			array(
				 'part'       => static::$part['playlists']['all'],
				 'channelId'  => $channel['id'],
				 'maxResults' => static::$limit,
			),
			$user
		);

		if(isset($channelPlaylists['result']['error'])) {
			d($channelPlaylists);
		}

		// Get channel related playlists
		$playlists = \Rest::get(
			static::$endpoints['playlists'],
			array(
				 'part'       => static::$part['playlists']['all'],
				 'id'         => implode(',', $playlists),
				 'maxResults' => static::$limit,
			),
			$user
		);

		if(isset($playlists['result']['error'])) {
			d($playlists);
		}

		$playlists['pageInfo']['totalResults'] += $channelPlaylists['pageInfo']['totalResults'];
		$playlists['items'] = array_merge($channelPlaylists['items'], $playlists['items']);

		$videos      = array();
		$dbPlaylists = array();

		if( $playlists) {
			if($playlists['pageInfo']['totalResults'] < static::$limit) {
				foreach($playlists['items'] as $playlist) {

					if( !in_array($playlist['snippet']['title'], static::$excludedPlaylists)) {
						$formatedData = array(
							'user_id'             => $user['username']['id'],
							'task_id'             => $taskId,
							'sync_task_id'        => $syncTaskId,
							'channel_id'          => $dbChannels[0]->id,
							'youtube_channel_id'  => $channel['id'],
							'youtube_playlist_id' => $playlist['id'],
							'etag'                => $playlist['etag'],
							'kind'                => static::$kind['playlist'],
							'title'               => $playlist['snippet']['title'],
							'description'         => $playlist['snippet']['description'],
							'videos_count'        => $playlist['contentDetails']['itemCount'],
							'videos_player'       => base64_encode($playlist['player']['embedHtml']),
							'created_at'          => date(DATE_TIME, strtotime($playlist['snippet']['publishedAt'])),
							'picture'             => $playlist['snippet']['thumbnails']['default']['url'],
							'privacy'             => $playlist['status']['privacyStatus'],
							'status'              => PlaylistsModel::STATUS_ACTIVE
						);

						$newplayList = PlaylistsModel::create($formatedData)->save();
						$dbPlaylists[] = $newplayList;
					}
				}
			}

			foreach($dbPlaylists as $playlist) {

				$stats['playlists']++;

				do {
					$payload = array(
						'part'       => static::$part['playlistItems']['minimal'],
						'playlistId' => $playlist->youtube_playlist_id,
						'maxResults' => static::$limit,
					);

					if(isset($vids['nextPageToken'])) {
						$payload['pageToken'] = $vids['nextPageToken'];
					}

					$vids = array();
					$vids = \Rest::get(
						static::$endpoints['playlistItems'],
						$payload,
						$user
					);

					if(isset($vids['result']['error'])) {
						d($vids);
					}

					if($vids['items']) {
						foreach($vids['items'] as $vid) {

							$formatedData = array(
								'user_id'             => $user['username']['id'],
								'task_id'             => $taskId,
								'sync_task_id'        => $syncTaskId,
								'channel_id'          => $playlist->channel_id,
								'playlist_id'         => $playlist->id,
								'youtube_channel_id'  => $playlist->youtube_channel_id,
								'youtube_playlist_id' => $playlist->youtube_playlist_id,
								'youtube_video_id'    => $vid['id'],
								'etag'                => $vid['etag'],
								'kind'                => static::$kind['video'],
								'title'               => $vid['snippet']['title'],
								'created_at'          => date(DATE_TIME, strtotime($vid['snippet']['publishedAt'])),
								'picture'             => (isset($vid['snippet']['thumbnails']) ? $vid['snippet']['thumbnails']['default']['url'] : '' ),
								'position'            => $vid['snippet']['position'],
								'video_link'          => $vid['contentDetails']['videoId'],
								'privacy'             => $vid['status']['privacyStatus'],
								'status'              => VideosModel::STATUS_ACTIVE
							);

							$newVideos[] = $formatedData;

							$stats['videos']++;
						}
					}

				} while(isset($vids['nextPageToken']) && $vids['nextPageToken']);
			}

			if($newVideos) {
				VideosModel::insertBatch($newVideos);
			}
		}

		$newSubscriptions = array();
		do {
			$payload = array(
				'part'       => static::$part['subscriptions']['minimal'],
				'mine'       => 'true',
				'maxResults' => static::$limit,
			);

			if(isset($subscriptions['nextPageToken'])) {
				$payload['pageToken'] = $subscriptions['nextPageToken'];
			}

			$subscriptions = \Rest::get(
				static::$endpoints['subscriptions'],
				$payload,
				$user
			);

			if(isset($subscriptions['result']['error'])) {
				d($subscriptions);
			}

			if($subscriptions['items']) {
				foreach($subscriptions['items'] as $sub) {

					$formatedData = array(
						'user_id'            => $user['username']['id'],
						'task_id'            => $taskId,
						'sync_task_id'       => $syncTaskId,
						'youtube_channel_id' => $sub['id'],
						'etag'               => $sub['etag'],
						'kind'               => static::$kind['subscription'],
						'title'              => $sub['snippet']['title'],
						'description'        => $sub['snippet']['description'],
						'created_at'         => date(DATE_TIME, strtotime($sub['snippet']['publishedAt'])),
						'picture'            => (isset($sub['snippet']['thumbnails']) ? $sub['snippet']['thumbnails']['default']['url'] : ''),
						'channel_link'       => $sub['snippet']['resourceId']['channelId'],
						'status'             => SubscriptionsModel::STATUS_ACTIVE
					);

					$newSubscriptions[] = $formatedData;

					$stats['subscriptions']++;
				}
			}

		} while(isset($subscriptions['nextPageToken']) && $subscriptions['nextPageToken']);

		if($newSubscriptions) {
			SubscriptionsModel::insertBatch($newSubscriptions);
		}

		return $stats;
	}

	public static function share($user)
	{
		// Stats
		$stats = array(
			'playlists'     => array(),
			'subscriptions' => array(),
		);

		// Channels
		$channels = \Rest::get(
			static::$endpoints['channels'],
			array(
				 'part'       => static::$part['channels']['minimal'],
				 'maxResults' => static::$limit,
				 'mine'       => 'true',
			)
			, $user
		);

		$playlists = array();
		$channel = current($channels['items']);

		// Get channel playlists
		$channelPlaylists = \Rest::get(
			static::$endpoints['playlists'],
			array(
				 'part'       => static::$part['playlists']['all'],
				 'channelId'  => $channel['id'],
				 'maxResults' => static::$limit,
			),
			$user
		);

		if(isset($channelPlaylists['result']['error'])) {
			d($channelPlaylists);
		}
		$playlists = $channelPlaylists['items'];

		if( $playlists) {
			foreach($playlists as $playlist) {
				if( !in_array($playlist['snippet']['title'], static::$excludedPlaylists)) {
					$stats['playlists'][] = array(
						'id'      => $playlist['id'],
						'name'    => $playlist['snippet']['title'],
						'picture' => $playlist['snippet']['thumbnails']['default']['url'],
						'data'    => $playlist
					);
				}
			}
		}

		do {
			$payload = array(
				'part'       => static::$part['subscriptions']['minimal'],
				'mine'       => 'true',
				'maxResults' => static::$limit,
			);

			if(isset($subscriptions['nextPageToken'])) {
				$payload['pageToken'] = $subscriptions['nextPageToken'];
			}

			$subscriptions = \Rest::get(
				static::$endpoints['subscriptions'],
				$payload,
				$user
			);

			if(isset($subscriptions['result']['error'])) {
				d($subscriptions);
			}

			if($subscriptions['items']) {
				foreach($subscriptions['items'] as $sub) {
					$stats['subscriptions'][] = array(
						'id'      => $sub['snippet']['resourceId']['channelId'],
						'name'    => $sub['snippet']['title'],
						'picture' => (isset($sub['snippet']['thumbnails']) ? $sub['snippet']['thumbnails']['default']['url'] : ''),
						'data'    => $sub
					);
				}
			}

		} while(isset($subscriptions['nextPageToken']) && $subscriptions['nextPageToken']);

		return $stats;
	}

	public static function shared($task)
	{
		$stats = array(
			'playlists'     => array(),
			'subscriptions' => array(),
		);

		// Get playlists
		$playlists = PlaylistsModel::withVideos($task['user_id'], $task['id']);
		if($playlists) {
			foreach($playlists as $playlist) {
				$stats['playlists'][] = array(
					'id'      => $playlist['youtube_playlist_id'],
					'name'    => $playlist['title'],
					'picture' => $playlist['picture'],
					'data'    => $playlist
				);
			}
		}

		// Get subscriptions
		$subscriptions = SubscriptionsModel::all(array('sync_task_id' => $task['id']))->toArray();
		if($subscriptions) {
			foreach($subscriptions as $sub) {
				$stats['subscriptions'][] = array(
					'id'      => $sub['channel_link'],
					'name'    => $sub['title'],
					'picture' => $sub['picture'],
					'data'    => $sub
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
			'playlists'     => 0,
			'videos'        => 0,
			'subscriptions' => 0,
		);

		// Get source data
		$playlists            = PlaylistsModel::withVideos($source['username']['id'], $syncTaskId);
		$destinationPlaylists = PlaylistsModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId))->toArray();
		$destinationNames     = static::_getColumn($destinationPlaylists, 'title');

		$subscriptions        = SubscriptionsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId))->toArray();

		$syncedVideos        = VideosModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId))->column('video_link');
		$syncedSubscriptions = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'destination_id' => $destination['username']['id'], 'kind' => static::$kind['subscription'], 'status' => MigratedDataModel::STATUS_ACTIVE))->column('identifier');

		$counter = 0;
		$batches = array();
		$batch   = md5(time());

		// Add data to destionation Youtube
		if($playlists) {
			foreach($playlists as $playlist) {
				if(intVal($playlist['videos_count']) > 0) {
					if($counter == 998) {
						$batch   = md5(time());
						$counter = 0;
					}

					// Create Playlist
					$payload = array();
					$payload['status']['privacyStatus'] = 'private';

					// Whitelisting used for share feature
					$whiteListed = true;
					if($whitelist) {
						if(isset($whitelist['playlists']) && $whitelist['playlists']) {
							if( !in_array($playlist['youtube_playlist_id'], $whitelist['playlists'])) {
								$whiteListed = false;
							}
						}
					}

					// New
					if( (!in_array($playlist['title'], array_keys($destinationNames)) && $whiteListed) || $ignoreUpdate  ) {

						// Create Playlist
						$payload['snippet']['title'] = $playlist['title'];
						$newPlaylist = \Rest::postJSON(static::$endpoints['playlists'] . '?part=status,snippet', $payload, $destination);

						$stats['playlists']++;

						if(isset($newPlaylist['result']['error'])) {
							d($newPlaylist);
						}

						$syncPlaylist = MigratedDataModel::create();
						$syncPlaylist->source_id      = $source['username']['id'];
						$syncPlaylist->destination_id = $destination['username']['id'];
						$syncPlaylist->task_id        = 0;
						$syncPlaylist->sync_task_id   = $syncTaskId;
						$syncPlaylist->table          = PlaylistsModel::$schema['table'];
						$syncPlaylist->table_id       = $playlist['id'];
						$syncPlaylist->kind           = static::$kind['playlist'];
						$syncPlaylist->identifier     = $playlist['youtube_playlist_id'];
						$syncPlaylist->name           = $playlist['title'];
						$syncPlaylist->created        = date(DATE_TIME);
						$syncPlaylist->status         = MigratedDataModel::STATUS_ACTIVE;
						$syncPlaylist->save();

						// Update playlist with new id
						if( !$ignoreUpdate ) {
							$oldPlaylist = PlaylistsModel::first($playlist['id']);
							$oldPlaylist->new_youtube_id = $newPlaylist['id'];
							$oldPlaylist->save();
						}

					// Existing
					} else {
						$play = PlaylistsModel::first(array('user_id' => $destination['username']['id'], 'title' => $playlist['title']))->toArray();
						$newPlaylist['id'] = $play['youtube_playlist_id'];
					}

					$counter++;

					// Add videos
					if($playlist['videos']) {
						foreach($playlist['videos'] as $k => $video) {

							if( !in_array($video['video_link'], $syncedVideos) || $ignoreUpdate) {

								if($counter == 998) {
									$batch   = md5(time());
									$counter = 0;
								}

								$counter++;

								$payload = array();
								$payload['snippet']['playlistId'] = $newPlaylist['id'];
								$payload['snippet']['resourceId']['kind'] = static::$kind['video'];
								$payload['snippet']['resourceId']['videoId'] = $video['video_link'];

								$payload = json_encode($payload);

								$batches[$batch][$newPlaylist['id']][] = <<<EOD

POST https://www.googleapis.com/youtube/v3/playlistItems?part=snippet
Content-Type:  application/json
Authorization: Bearer {$destination['credentials']['access_token']}

{$payload}

EOD;
								$stats['videos']++;

								$syncPlaylist = MigratedDataModel::create();
								$syncPlaylist->source_id      = $source['username']['id'];
								$syncPlaylist->destination_id = $destination['username']['id'];
								$syncPlaylist->task_id        = 0;
								$syncPlaylist->sync_task_id   = $syncTaskId;
								$syncPlaylist->table          = VideosModel::$schema['table'];
								$syncPlaylist->table_id       = $video['id'];
								$syncPlaylist->kind           = static::$kind['video'];
								$syncPlaylist->identifier     = $video['video_link'];
								$syncPlaylist->name           = $video['title'];
								$syncPlaylist->created        = date(DATE_TIME);
								$syncPlaylist->status         = MigratedDataModel::STATUS_ACTIVE;
								$syncPlaylist->save();

							}
						}
					}
				}
			}
		}

		// Add subscriptions
		if($subscriptions) {
			foreach($subscriptions as $subscription) {

				// Whitelisting used for share feature
				$whiteListed = true;
				if($whitelist) {
					if(isset($whitelist['subscriptions']) && $whitelist['subscriptions']) {
						if( !in_array($subscription['channel_link'], $whitelist['subscriptions'])) {
							$whiteListed = false;
						}
					}
				}

				if( (!in_array($subscription['channel_link'], $syncedSubscriptions) && $whiteListed) || $ignoreUpdate ) {

					if($counter == 998) {
						$batch   = md5(time());
						$counter = 0;
					}

					$counter++;

					$payload = array();
					$payload['snippet']['resourceId']['kind'] = static::$kind['channel'];
					$payload['snippet']['resourceId']['channelId'] = $subscription['channel_link'];

					$payload = json_encode($payload);

					$batches[$batch][$subscription['channel_link']][] = <<<EOD

POST https://www.googleapis.com/youtube/v3/subscriptions?part=snippet
Content-Type:  application/json
Authorization: Bearer {$destination['credentials']['access_token']}

{$payload}

EOD;

					$stats['subscriptions']++;

					$syncPlaylist = MigratedDataModel::create();
					$syncPlaylist->source_id      = $source['username']['id'];
					$syncPlaylist->destination_id = $destination['username']['id'];
					$syncPlaylist->task_id        = 0;
					$syncPlaylist->sync_task_id   = $syncTaskId;
					$syncPlaylist->table          = SubscriptionsModel::$schema['table'];
					$syncPlaylist->table_id       = $subscription['id'];
					$syncPlaylist->kind           = static::$kind['subscription'];
					$syncPlaylist->identifier     = $subscription['channel_link'];
					$syncPlaylist->name           = $subscription['title'];
					$syncPlaylist->created        = date(DATE_TIME);
					$syncPlaylist->status         = MigratedDataModel::STATUS_ACTIVE;
					$syncPlaylist->save();

				}
			}
		}

		if($batches) {
			foreach($batches as $key => $batch) {
				for($i = 0; $i < 500; $i++) {
					$body = '';

					$y = md5($i);

					foreach($batch as $k => $set) {
						$keys = $k . '-' . $i;
						if(isset($set[$i])) {

							$body .=
								<<<EOD
--$y
Content-Type: application/http
Content-Transfer-Encoding: binary
MIME-Version: 1.0
Content-ID:$keys
$set[$i]

EOD;
						}
					}

					if($body) {
						$body .= "--" . $y . "--";

						$destination['boundary'] = $y;
						$insert = \Rest::postRaw('https://www.googleapis.com/batch', $body, $destination);
					}
				}
			}
		}

		return $stats;
	}

	public static function _clean($destination, $source, $syncTaskId = 0, $whitelist = array())
	{
		$service = ServicesModel::first(array('library' => 'Youtube'))->toArray();

		$task = TasksServicesModel::first(array('task_id' => $syncTaskId, 'service_id' => $service['id']))->toArray();
		$task['stats'] = json_decode($task['stats'], true);

		// Playlists
		$playlists = PlaylistsModel::all(array('user_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId))->toArray();

		// Videos
		$videos         = array();
		$migratedVideos = MigratedDataModel::all(array('source_id' => $source['username']['id'], 'sync_task_id' => $syncTaskId, 'kind' => static::$kind['video']));
		if($migratedVideos) {
			$migratedVideos = $migratedVideos->toArray();

			if($migratedVideos) {
				$destinationPlaylists = PlaylistsModel::all(array('user_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId));

				if($destinationPlaylists) {
					$destinationPlaylists = $destinationPlaylists->toArray();

					if($destinationPlaylists) {

						foreach($destinationPlaylists as $playlist) {

							foreach($migratedVideos as $video) {
								$payload = array(
									'part'       => static::$part['playlistItems']['minimal'],
									'playlistId' => $playlist['youtube_playlist_id'],
									'videoId'    => $video['identifier']
								);

								$vids = array();
								$vids = \Rest::get(
									static::$endpoints['playlistItems'],
									$payload,
									$destination
								);

								if(isset($vids['result']['error'])) {
									d($vids);
								}

								if($vids && $vids['items']) {
									foreach($vids['items'] as $vid) {
										$videos[] = $vid['id'];
									}
								}
							}
						}
					}
				}
			}
		}

		// Subscriptions
		$newSubscriptions = array();
		$oldSubscriptions = MigratedDataModel::all(array('destination_id' => $destination['username']['id'], 'sync_task_id' => $syncTaskId))->column('identifier');
		do {
			$payload = array(
				'part'       => static::$part['subscriptions']['minimal'],
				'mine'       => 'true',
				'maxResults' => static::$limit,
			);

			if(isset($subscriptions['nextPageToken'])) {
				$payload['pageToken'] = $subscriptions['nextPageToken'];
			}

			$subscriptions = \Rest::get(
				static::$endpoints['subscriptions'],
				$payload,
				$destination
			);

			if(isset($subscriptions['result']['error'])) {
				d($subscriptions);
			}

			if($subscriptions['items']) {
				foreach($subscriptions['items'] as $sub) {
					$newSubscriptions[] = $sub;
				}
			}

		} while(isset($subscriptions['nextPageToken']) && $subscriptions['nextPageToken']);


		switch(true) {

			# Share / Clean
			############################################################################
			case ( !empty($whitelist) ):

				// Playlist
				if( $playlists) {
					foreach($playlists as $playlist) {
						if( isset($whitelist['playlists']) && in_array($playlist['youtube_playlist_id'], $whitelist['playlists']) ) {
							\Rest::delete(static::$endpoints['playlists'], array('id' => $playlist['youtube_playlist_id']), $destination);
						}
					}
				}

				// Subscriptions
				if($newSubscriptions) {
					foreach($newSubscriptions as $sub) {
						if( isset($whitelist['subscriptions']) && in_array($sub['snippet']['resourceId']['channelId'], $whitelist['subscriptions']) ) {
							\Rest::delete(static::$endpoints['subscriptions'], array('id' => $sub['id']), $destination);
						}
					}
				}

				break;

			# Move
			############################################################################
			case ( $destination['username']['id'] == $source['username']['id'] ):

				// Playlist
				if( $playlists ) {
					foreach($playlists as $playlist) {
						\Rest::delete(static::$endpoints['playlists'], array('id' => $playlist['youtube_playlist_id']), $destination);
					}
				}

				// Subscriptions
				if($newSubscriptions) {
					foreach($newSubscriptions as $sub) {
						\Rest::delete(static::$endpoints['subscriptions'], array('id' => $sub['id']), $destination);
					}
				}
				break;

			# Migrate / Sync
			############################################################################
			default:

				// Playlist
				if( $playlists && $task['stats']['playlists'] ) {
					foreach($playlists as $playlist) {
						if( $playlist['new_youtube_id']) {
							\Rest::delete(static::$endpoints['playlists'], array('id' => $playlist['new_youtube_id']), $destination);
						}
					}
				}

				// Videos
				if( $videos && $task['stats']['videos'] ) {
					foreach($videos as $videoId) {
						\Rest::delete(static::$endpoints['playlistItems'], array('id' => $videoId), $destination);
					}
				}

				// Subscriptions
				if($newSubscriptions) {
					foreach($newSubscriptions as $sub) {
						if(in_array($sub['snippet']['resourceId']['channelId'], $oldSubscriptions) && $task['stats']['subscriptions']) {
							\Rest::delete(static::$endpoints['subscriptions'], array('id' => $sub['id']), $destination);
						}
					}
				}
				break;
		}
	}

	public static function _cleanDB($syncTaskId)
	{
		// Clear DB data
		MigratedDataModel::softDelete(array('sync_task_id' => $syncTaskId));
//		ChannelsModel::remove(array('sync_task_id' => $syncTaskId));
//		PlaylistsModel::remove(array('sync_task_id' => $syncTaskId));
//		VideosModel::remove(array('sync_task_id' => $syncTaskId));
//		SubscriptionsModel::remove(array('sync_task_id' => $syncTaskId));
	}
}