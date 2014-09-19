<?
	namespace app\models;

	class BackupsModel extends \Model
	{
		public static $schema = array(
			'table'  => 'backups',
			'fields' => array('id', 'user_id', 'task_id', 'sync_task_id', 'parent_id', 'entity_id', 'entity_type', 'entity_parent', 'entity_title', 'entity_picture', 'entity_new_id', 'entity', 'created')
		);

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