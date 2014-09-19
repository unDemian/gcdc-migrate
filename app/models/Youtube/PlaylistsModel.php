<?
	namespace app\models\Youtube;

	use app\models\Videos;

	class PlaylistsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_youtube_playlists',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'channel_id', 'youtube_channel_id', 'youtube_playlist_id', 'new_youtube_id', 'etag', 'kind', 'title', 'description', 'videos_count', 'videos_player', 'created_at', 'picture', 'privacy', 'status' )
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

		public static function withVideos($userId, $taskId)
		{
			$playlists = static::all(array('user_id' => $userId, 'sync_task_id' => $taskId))->toArray();

			if($playlists) {
				foreach($playlists as $key => $playlist) {
					$playlists[$key]['videos'] = VideosModel::all(array('playlist_id' => $playlist['id']))->toArray();
				}
			}

			return $playlists;
		}
	}