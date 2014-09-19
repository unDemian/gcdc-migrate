<?
	set_time_limit(0);

	# General Constants
	define('DEBUG',  	 		 		true);
	define('URI_INFO',                  'PATH_INFO');

	# mySQL
	define('MYSQL_HOSTNAME', 			'mysql:host=localhost');
	define('MYSQL_USERNAME', 			'root');
	define('MYSQL_PASSWORD', 			'xxxxxxxxxxxxxxxx');
	define('MYSQL_DATABASE', 			'miggrate');

	# oAuth
	define('OAUTH_CLIENT_ID', 			'xxxxxxxxxxxxxxxx.apps.googleusercontent.com');
	define('OAUTH_CLIENT_SECRET',		'xxxxxxxxxxxxxxxx');
	define('OAUTH_REDIRECT_URI',		BASE_URL . 'oauth');