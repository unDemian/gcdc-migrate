<?
	session_start();

	# Global Settings
	header('Access-Control-Allow-Origin: *');
	error_reporting(E_ALL);

	# Global Constants
	define('BASE_URL',              ($_SERVER['HTTPS'] === 'off' ? 'http://': 'https://') . $_SERVER['SERVER_NAME'] . '/');
	define('BASE_PATH',	 			str_replace("\\", "/", realpath(dirname(__FILE__))) . '/');
	define('APP_PATH',      	  	BASE_PATH . 'app/');
	define('VIEWS_PATH',      	  	APP_PATH . 'views/');
	define('CORE_PATH',   		    APP_PATH . 'core/');
	define('LIBRARIES_PATH',   		APP_PATH . 'libraries/');
	define('MODELS_PATH',   		APP_PATH . 'models/');
	define('DATE',               	'm/d/Y');
	define('DATE_TIME',         	'Y-m-d H:i:s');

	# URL Constants
	define('ASSETS',          		BASE_URL . 'assets/');
	define('IMAGE',           		ASSETS . 'images/');
	define('CSS',             		ASSETS . 'css/');
	define('JS',              		ASSETS . 'js/');
	define('FONT',            		ASSETS . 'fonts/');

	# Select config
	if($_SERVER['SERVER_NAME'] === 'migrate.edu') {
		require APP_PATH . 'config/development.php';
	} else {
		require APP_PATH . 'config/production.php';
	}

	# Start application (autoload)
	require APP_PATH . 'autoload.php';
