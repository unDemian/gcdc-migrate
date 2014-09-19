<?
	namespace app\models;

	class IntroModel extends \Model
	{
		public static $schema = array(
			'table'  => 'intro',
			'fields' => array( 'id', 'group', 'page' )
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