<?
	namespace app\controllers;

	// Bootstrappin'
	set_include_path(get_include_path() . PATH_SEPARATOR . '.');
	require_once 'bootstrap.php';

	/**
	 * Class Error
	 * Error (404) page handler
	 *
	 * @package app\controllers
	 */
	class Error
	{
		public static function index()
		{
			$templateData = array(
				'template' => 'error/content',
				'title'    => 'Migrate - Not found',
				'bodyId'   => '404',
				'styles'   => array(
					'error.css'
				),
				'scripts'  => array(),
			);

			\Render::layout('template', $templateData);
		}
	}

	// Go Go Go!
	\Router::connect(__NAMESPACE__, 'Error');


