<?
	# Global Application Autoloader
	#####################################################################################

	/**
	 * Debug function
	 *
	 * @param $data
	 */
	function d($data)
	{
		if(DEBUG) {
			echo '<pre>';
			var_dump($data);

			$trace = debug_backtrace();
			$trace = array_reverse($trace);

			echo '<br />################################################################################<br />';

			foreach($trace as $item) {
				echo $item['file'] . ':' . $item['line'] . '<br />';
			}

			exit;
		}
	}

	/**
	 *  Autoload needed libraries
	 *
	 * @param $className
	 */
	function autoload($className)
	{
		// Libraries
		if(stripos($className, '\\')) {
			$filename = str_replace('\\', '/', $className);
			require_once BASE_PATH . $filename . '.php';

		// Core Libraries
		} else {
			require_once CORE_PATH . $className . '.php';
			if(method_exists($className, '__init')) {
				$className::__init();
			}
		}
	}

	# Register autoloader
	spl_autoload_register('autoload');