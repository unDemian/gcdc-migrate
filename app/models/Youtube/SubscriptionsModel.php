<?
	namespace app\models\Youtube;

	class SubscriptionsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_youtube_subscriptions',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'youtube_channel_id', 'etag', 'kind', 'title', 'description', 'created_at', 'picture', 'channel_link', 'status' )
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
	}