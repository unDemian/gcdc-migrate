<?
	# General Constants
	define('DEBUG',  	 		 		false);
	define('URI_INFO',                  'REQUEST_URI');

	# mySQL
	define('MYSQL_HOSTNAME', 			'mysql:unix_socket=/cloudsql/gcdc2013-migrate:pipe');
	define('MYSQL_USERNAME', 			'root');
	define('MYSQL_PASSWORD', 			'xxxxxxxxxxxxxxxx');
	define('MYSQL_DATABASE', 			'miggrate');

	# oAuth
	define('OAUTH_CLIENT_ID', 			'xxxxxxxxxxxxxxxx.apps.googleusercontent.com');
	define('OAUTH_CLIENT_SECRET',		'xxxxxxxxxxxxxxxx');
	define('OAUTH_REDIRECT_URI',		BASE_URL . 'oauth');