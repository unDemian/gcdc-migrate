<?
	namespace app\models\Blogger;

	class BlogsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_blogger_blogs',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'kind', 'blog_id', 'name', 'description', 'url', 'created_at', 'status' )
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