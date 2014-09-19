<?
	/**
	 * Class Util
	 */
	class Util
	{
		/**
		 * Resize the google profile pic from it's URL
		 *
		 * @param null $user
		 * @param int  $size
		 *
		 * @return string
		 */
		public static function profileImageUrl($user = null, $size = 28)
		{
			if($user) {
				if(isset($user['avatar'])) {
					$url = parse_url($user['avatar']);
					if(isset($url['query'])) {
						$url['query'] = 'sz=' . $size;
						return $url['scheme'] . '://' . $url['host'] . '/' . $url['path'] . '?' . $url['query'];
					} else {
						if($user['avatar']) {
							return $user['avatar'] . '?sz=' . $size;
						}
					}
				}
			}

			return Render::image('no-photo.jpg');
		}

		public static function listingEmail($email)
		{
			$temp = explode('@', $email);
			$email = $temp[0] . '@' . substr($temp[1], 0, 2) . '...';
			return $email;
		}

		// Notice Handling
		//////////////////////////////////////////////////////////////////////////////

		/**
		 * Check for notices
		 *
		 * @return bool
		 */
		public static function hasNotice()
		{
			return (isset($_SESSION['notice']));
		}

		/**
		 * Get / Set Notices
		 *
		 * @param array $params
		 *
		 * @return mixed
		 */
		public static function notice($params = array())
		{
			if($params) {
				if (session_status() == PHP_SESSION_NONE) {
					session_start();
				}
				$_SESSION['notice'] = $params;
			} else {
				$notices = $_SESSION['notice'];
				unset($_SESSION['notice']);
				return $notices;
			}
		}

		public static function dropdownStatus($selected = null, $section = null)
		{
			$results = array(
				0 => array('class' => 'default', 'text' => 'Scheduled'),
				1 => array('class' => 'warning', 'text' => 'In progress'),
				2 => array('class' => 'success', 'text' => 'Done'),
				3 => array('class' => 'warning', 'text' => 'Reverting'),
				4 => array('class' => 'info', 'text' => 'Reverted'),
			);

			if( !is_null($selected) && isset($results[$selected])) {
				return $results[$selected][$section];
			}

			return $results;
		}

		public static function countdown($date)
		{
			$date  = strtotime($date);
			$now = time();

			$difference = ($now - $date);
			$difference = (int) $difference;

			switch(true) {
				case ($difference < 60):
					$result = 'seconds ago';
					break;

				case ($difference > 60 && $difference < 3600):
					$minutes = round($difference / 60);
					$result = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
					break;

				case ($difference > 3600 && $difference < 86400):
					$hours = round($difference / 3600);
					$result = round($difference / 3600) . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
					break;

				case ($difference > 86400 && $difference < (86400 * 8)):
					$days = round($difference / 86400);
					$result = round($difference / 86400) . ' day' . ($days > 1 ? 's' : '') . ' ago';
					break;

				default:
					$result = date(DATE, $date);
					break;
			}

			return $result;
		}

		public static function countup($date)
		{
			$date  = strtotime($date);
			$now = time();

			$difference = ($date - $now);
			$difference = (int) $difference;

			switch(true) {
				case ($difference <= 0):
					$result = 'Expired';
					break;

				case ($difference < 60):
					$result = 'second' . ($difference > 1 ? 's' : '');
					break;

				case ($difference > 60 && $difference < 3600):
					$minutes = round($difference / 60);
					$result = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
					break;

				case ($difference > 3600 && $difference < 86400):
					$hours = round($difference / 3600);
					$result = round($difference / 3600) . ' hour' . ($hours > 1 ? 's' : '');
					break;

				case ($difference > 86400 && $difference < (86400 * 8)):
					$days = round($difference / 86400);
					$result = round($difference / 86400) . ' day' . ($days > 1 ? 's' : '');
					break;

				default:
					$result = date(DATE_TIME, $date);
					break;
			}

			return $result;
		}

		public static function elapsed($seconds)
		{

			switch(true) {

				case ($seconds == 0):
					$result = '-';
					break;

				case ($seconds < 60):
					$result = $seconds . ' second' . ($seconds > 1 ? 's' : '');
					break;

				case ($seconds > 60 && $seconds < 3600):
					$minutes = round($seconds / 60);
					$result = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
					break;

				case ($seconds > 3600 && $seconds < 86400):
					$hours = round($seconds / 3600);
					$result = $hours . ' hours' . ($hours > 1 ? 's' : '');
					break;

			}

			return $result;
		}
		
		public static function action($termination = false)
		{
			$action = $_SESSION['wizard']['action'];

			switch($action) {
				case 0:
					if($termination) {
						return 'migrating';
					}
					return 'migrate';
					break;
				case 1:
					if($termination) {
						return 'syncing';
					}
					return 'sync';
					break;

				case 2:
					if($termination) {
						return 'moving';
					}
					return 'move';
					break;
			}
		}

		public static function actionIcon($type = false)
		{
			switch($type) {
				case 'sync':
					return 'fa-retweet';
					break;

				case 'migrate':
					return 'fa-expand';
					break;

				case 'move':
					return 'fa-long-arrow-right';
					break;

				case 'clean':
					return 'fa-trash-o';
					break;

				case 'share':
					return 'fa-share-square-o';
					break;
			}
		}

		public static function wrap($text)
		{
			$max = 14;

			if(strlen($text) > $max) {
				return substr($text, 0, 14) . '...';
			}

			return $text;
		}
	}