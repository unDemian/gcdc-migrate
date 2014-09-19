<?
	/**
	 * Render class for layout and views
	 */
	class Render
	{
		/**
		 * Handles the inclusion of layouts and views. Accepts functions: layout($template, $view) | view($view) | css($css) | js($js) | image($image) | font($font) | link($link)
		 *
		 * @param $name
		 * @param $arguments
		 */	
		public static function __callStatic($name, $arguments)
		{
			$templateData = array();
			$return       = false;

			extract($arguments, EXTR_PREFIX_ALL, 'arg');

			switch($name) {
				case 'layout':
					$template     = $arg_0;
					$templateData = $arg_1;

					$includePath = VIEWS_PATH . $template . '.php';
					$viewPath    = VIEWS_PATH . $templateData['template'] . '.html.php';
					break;

				case 'view':
					$template = $view = $arg_0;

					if(isset($arg_1)) {
						$templateData = $arg_1;
					}

					if(isset($arg_2)) {
						$return = $arg_2;
					}

					$includePath = $viewPath = VIEWS_PATH . $view . '.html.php';
					break;

				case 'css': case 'image': case 'js': case 'font':
					return constant(strtoupper($name)) . $arg_0;
					break;

				case 'link':
					return BASE_URL . $arg_0;
					break;
			}

			if($templateData) {
				extract($templateData);
			}

			if(file_exists($viewPath)) {

				if($return) {
					ob_start();
					require $includePath;
					return ob_get_clean();
				} else {
					require $includePath;
				}

			} else {
				die('The ' . $name . ' file ' . $view . ' was not found.');
			}
		}
	}