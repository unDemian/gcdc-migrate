<?
	/**
	 * Class Router
	 * Route URL to classes and methods
	 */
	class Router
	{
		public static $class  = false;
		public static $method = false;

		/**
		 * Actual routing + sanitizing data
		 *
		 * @param       $class
		 * @param array $params
		 */
		public static function connect($namespace, $class, $params = array())
		{
			$defaults = array(
				'indexPage'     => 'index',
				'loginPage' 	=> false,
				'loginRedirect' => false,
			);

			static::$class = strtolower($class);
			$class = $namespace . '\\' . $class;

			$params += $defaults;
			extract($params);

			// Authenticated controllers
			if( $loginPage ) {
				Auth::checkLogin($loginRedirect, $loginPage);
			}

			$method     = $indexPage;
			$parameters = array();

			if(isset($_SERVER[URI_INFO])) {
				$url = explode('/', substr($_SERVER[URI_INFO], 1));
				array_shift($url);

				if($url) {
					foreach($url as $key => $element) {
						if( !$key && !is_numeric($element)) {
							$method = $element;
						} else {
							$parameters[] = $element;
						}
					}
				}
			}

			// Check availability
			try {
				$methodInfo = new \ReflectionMethod($class, $method);

				// Methods that start with _ are not accesible from browser
				$name = $methodInfo->getName();
				if($name[0] == '_') {
					$method = $indexPage;
				}

				$methodParams = $methodInfo->getParameters();

				// Force cast parameters by arguments default value
				if($methodParams) {
					foreach($methodParams as $parameterKey => $parameterValue) {
						try {
							$defaultValue = $parameterValue->getDefaultValue();
							$type = gettype($defaultValue);

							if($defaultValue) {
								unset($methodParams[$parameterKey]);
							}

//							settype($parameters[$parameterKey], $type);
						} catch(\Exception $e) {
							continue;
						}
					}
				}

//				if(count($methodParams) != count($parameters)) {
//					$parameters = array();
//				}

			} catch(\Exception $e) {
				$method = $indexPage;
			}

			static::$method = $method;

			call_user_func_array($class . '::' . $method, $parameters);
			return;
		}

		public static function refresh()
		{
			header('refresh: 0;');
		}

		/**
		 * Create a redirect header
		 *
		 * @param $url
		 */
		public static function redirect($url)
		{
			if(stripos($url, '://') !== false) {
				header('Location: ' . $url);
				exit;
			}

			if($url && is_string($url)) {
				header('Location: ' . BASE_URL . $url);
				exit;
			}

			header('Location: ' . BASE_URL);
			exit;
		}

		/**
		 * Get referral
		 *
		 * @return string
		 */
		public static function referer()
		{
			return (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : BASE_URL . 'dashboard');
		}

	}