<?
	/**
	 * Class Controller
	 * Common controller extended by all controllers
	 */
	class Controller
	{
		public static function __init()
		{
			Auth::checkLogin();
		}
	}