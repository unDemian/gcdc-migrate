<?
	/**
	 * Class Rest
	 * stream wrapper in order to do rest requests
	 */
	class Rest
	{
		/**
		 * Handles the requests for post() and get()
		 *
		 * @param $name
		 * @param $arguments
		 */
		public static function __callStatic($name, $arguments)
		{
			$response = static::_transfer($name, $arguments);

			if($response && $name != 'delete') {
				$result = static::_reponse($response);
				if($result) {
					if( is_object($result) || !isset($result['error']) ) {
						return $result;
					} else {

						// Expired token ?
						if(isset($result['error']['message']) && $result['error']['message'] == Auth::$_errors['oAuthTokenExpired']) {
							if(isset($arguments[2])) {
								Authorization::oAuthRefreshToken($arguments[2], 'updateSession', 'force');
								call_user_func(array('Rest', $name), $arguments);
							} else {
								return compact('result', 'name', 'arguments', 'response');
							}
						} else {
							if(isset($result['error']['message']) && $result['error']['message'] != 'Forbidden' && $result['error']['message'] != 'Not Found') {
								return compact('result', 'name', 'arguments', 'response');
							}
						}
					}
				} else {
					return compact('result', 'name', 'arguments', 'response');
				}
			} else {
				if($name != 'delete') {
					return compact('result', 'name', 'arguments', 'response');
				}
			}
		}

		/**
		 * Create actual request. Allowed: post / get
		 *
		 * @param $method
		 * @param $params
		 *
		 * @return string
		 */
		private static function _transfer($method, $params)
		{
			// Parse arguments
			$url   = $params[0];
			$data  = isset($params[1]) ? $params[1] : array();
			$user  = isset($params[2]) ? $params[2] : array();

			$protocol = array(
				'http' => array(
					'method'        => strtoupper($method),
					'ignore_errors' => true,
				)
			);

			switch($method) {
				case 'get': case 'delete':
					if($data) {
						$url = $url . '?' . http_build_query($data);
					}
					break;

				case 'post':
					$protocol['http']['content'] = http_build_query($data);
					$protocol['http']['header']  = 'Content-type: application/x-www-form-urlencoded';
					break;

				case 'postJSON':
					$protocol['http']['content'] = json_encode($data);
					$protocol['http']['header']  = 'Content-Type: application/json';
					$protocol['http']['method']  = 'post';
					break;

				case 'postRaw':
					$protocol['http']['content'] = $data;
					$protocol['http']['header']  = 'Content-Type: multipart/mixed; boundary=' . $user['boundary'];
					$protocol['http']['method']  = 'post';
					break;

				case 'postXML':
					$protocol['http']['content'] = $data;
					$protocol['http']['header']  = 'Content-Type: application/atom+xml; charset=UTF-8';
					$protocol['http']['method']  = 'post';
					break;

				case 'putXML':
					$protocol['http']['content'] = $data;
					$protocol['http']['header']  = 'Content-Type: application/atom+xml; charset=UTF-8';
					$protocol['http']['method']  = 'put';
					break;

				case 'deleteXML':
					$protocol['http']['content'] = $data;
					$protocol['http']['header']  = 'Content-Type: application/atom+xml; charset=UTF-8';
					$protocol['http']['header']  = 'If-Match: *';
					$protocol['http']['method']  = 'delete';
					break;
			}

			// Bearer
			if($user) {

				$tokens = Auth::oAuthRefreshToken($user, 'updateSession');

				if(isset($protocol['http']['header'])) {
					$protocol['http']['header'] .= PHP_EOL . 'Authorization: Bearer ' . $user['credentials']['access_token'];
				} else {
					$protocol['http']['header'] = 'Authorization: Bearer ' . $user['credentials']['access_token'];
				}
			}

			$context = stream_context_create($protocol);
			if(is_array($url)) {
				d($url);
			}
			return file_get_contents($url, false, $context);
		}

		/**
		 * Parse Response
		 */
		private static function _reponse($response = null)
		{
			if($response) {
				$json = json_decode($response, true);
				if($json) {
					return $json;
				}

				if(stripos($response, '--batch_') !== false) {
					return array();
				}

				syslog(LOG_CRIT, json_encode($response));

				header("Content-type: text/xml; charset=utf-8");
				echo($response);

				// XML ?
				libxml_use_internal_errors(true);

				$doc = simplexml_load_string($response,'SimpleXMLElement');

				if ($doc) {
					return $doc;

				} else {
					$errors = libxml_get_errors();
					if($errors) {
						foreach ($errors as $error) {
							syslog(LOG_CRIT, $error->message);
						}

						libxml_clear_errors();
					} else {
						syslog(LOG_CRIT, $response);
					}

					return array();
				}
			}
			return array();
		}
	}