<?
	namespace app\models\Blogger;

	class PagesModel extends \Model
	{
		public static $schema = array(
			'table'  => 'z_blogger_pages',
			'fields' => array( 'id', 'user_id', 'task_id', 'sync_task_id', 'blog_id', 'blogger_blog_id', 'kind', 'page_id', 'page_status', 'title', 'content', 'created_at', 'author_id', 'author_name', 'author_url', 'author_avatar', 'status' )
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