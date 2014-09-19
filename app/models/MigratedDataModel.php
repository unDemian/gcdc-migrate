<?
	namespace app\models;

	class MigratedDataModel extends \Model
	{
		public static $schema = array(
			'table'  => 'migrated_data',
			'fields' => array( 'id', 'source_id', 'destination_id', 'task_id', 'sync_task_id', 'table', 'table_id', 'kind', 'identifier', 'name', 'unique', 'created', 'status' )
		);

		const STATUS_ACTIVE = 1;
		const STATUS_DELETED = 0;

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

		public static function softDelete($conditions = array())
		{
			$query = 'UPDATE ' . static::$schema['table'] . ' SET status = ' . static::STATUS_DELETED . ' WHERE sync_task_id = ' . $conditions['sync_task_id'];
			static::execute($query);
		}
	}