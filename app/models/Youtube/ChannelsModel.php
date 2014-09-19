<?
	namespace app\models\Youtube;

	class ChannelsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_youtube_channels',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'etag', 'kind', 'channel_id', 'title', 'description', 'created_at', 'picture', 'status' )
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

		public static function withPlaylists()
		{
			$query = "
				SELECT
					Channels.*
				FROM
					z_youtube_channels Channels
				WHERE
					Channels.status = " . static::STATUS_ACTIVE;

			$channels = array();
			$result   = static::execute($query)->group();

			if($result) {
				foreach($result as $channelId => $channel) {
					$channels[$channelId] = array_merge($channel[0], array('id' => $channelId));
				}
			}

			$playlists = PlaylistsModel::all(array('conditions' => array('channel_id' => array_keys($channels), 'status' => PlaylistsModel::STATUS_ACTIVE), 'order' => array('title' => 'ASC')))->toArray();

			if($playlists) {
				foreach($playlists as $playlist) {
					if(isset($channels[$playlist['channel_id']])) {
						$channels[$playlist['channel_id']]['playlists'][] = $playlist;
					}
				}
			}

			return $channels;
		}
	}