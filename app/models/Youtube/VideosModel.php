<?
	namespace app\models\Youtube;

	class VideosModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_youtube_videos',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'channel_id', 'playlist_id', 'youtube_channel_id', 'youtube_playlist_id', 'youtube_video_id', 'etag', 'kind', 'title', 'created_at', 'picture', 'position', 'video_link', 'privacy', 'status' )
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

		public static function perPlaylist($userId, $syncTaskId)
		{
			$final = array();
			$results = static::all(array('user_id' => $userId, 'sync_task_id' => $syncTaskId));
			if($results) {
				$results = $results->toArray();
				if($results) {
					foreach($results as $result) {
						$final[$result['playlist_id']][] = $result['video_link'];
					}
				}
			}

			return $final;
		}

	}