<?
	/**
	 * Class Model
	 */
	class Model
	{
		public static $db = null;

		public $settings = array(
			'handler'      => null,
			'method'       => 'fetch',
		);

		// Magic Methods
		//////////////////////////////////////////////////////////////////////////////////////////

		public function __construct($params = array())
		{
			$this->settings = $params;
		}

		/**
		 * Create the database connection ( when loaded)
		 *
		 */
		public static function __init()
		{
			try {
				if(static::$db == null) {
					static::$db = new \PDO( MYSQL_HOSTNAME . ';dbname=' . MYSQL_DATABASE . ';charset=utf8', MYSQL_USERNAME, MYSQL_PASSWORD);
				}

				if(DEBUG) {
					static::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				}

			} catch( Exception $e) {
				die($e->getMessage());
			}
		}

		/**
		 * Set values to object ( from PDO fetch)
		 *
		 * @param $name
		 * @param $value
		 */
		public function __set($name, $value)
		{
			$class = get_called_class();

			if( !in_array($name, array_values($class::$schema['fields'])) ) {
				die('There is no mapping for field "' . $name . '" in the ' . $class . ' model.');
			} else {
				$this->$name = $value;
			}
		}

		public function __get($name)
		{
			if(isset($this->$name)) {
				return $this->$name;
			}
			return null;
		}

		private static function _queryfier($params = array())
		{
			$query = '';

			if($params) {

				$query .= ' WHERE ';

				// Integer
				if( ! is_array($params)) {
					$query .= 'id = ' . $params;
				} else {

					// Simple condition
					if( !isset($params['conditions'])) {
						foreach($params as $field => $value) {
							if(is_array($value) && $value) {
								if(is_numeric($field)) {
									$query .= ' ( ';
									foreach($value as $fie => $val) {
										$query .= '`' . $fie . '` = "' . $val . '" OR ';
									}
									$query = substr($query, 0, -3);
									$query .= ' ) AND ';

								} else {
									$query .= '`' . $field . '` IN ("' . join('","', $value) . '") AND ';
								}
							} else {
								if(is_array($value) && empty($value)) {
									continue;
								} else {
									$query .= '`' . $field . '` = "' . $value . '" AND ';
								}
							}
						}
						$query = substr($query, 0, -4);

					// Combinated
					} else {

						if(isset($params['conditions']) && $params['conditions']) {
							foreach($params['conditions'] as $field => $value) {
								if(is_array($value) && $value) {
									if(is_numeric($field)) {
										$query .= ' ( ';
										foreach($value as $fie => $val) {
											$query .= '`' . $fie . '` = "' . $val . '" OR ';
										}
										$query = substr($query, 0, -3);
										$query .= ' ) AND ';

									} else {
										$query .= '`' . $field . '` IN ("' . join('","', $value) . '") AND ';
									}
								} else {
									$query .= '`' . $field . '` = "' . $value . '" AND ';
								}
							}
							$query = substr($query, 0, -4);
						}

						if(isset($params['order']) && $params['order']) {
							$query .= ' ORDER BY ';
							foreach($params['order'] as $field => $order) {
								$query .= $field . ' ' . $order . ', ';
							}
							$query = substr($query, 0, -2);
						}
					}
				}
			}

			return $query;
		}

		// CRUD Methods
		//////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * Retrieving methods. Allowed: first(), find(), all()
		 *
		 * @param       $name
		 * @param array $arguments
		 *
		 * @return mixed
		 */
		public static function __callStatic($name, $arguments = array())
		{
			switch($name) {
				case 'find':
					$method = 'fetch';
					break;

				case 'all':
					$method = 'fetchAll';
					break;

				default:
					die('Method not allowed in model!');
					return;
					break;
			}

			$class = get_called_class();

			$query = 'SELECT `' . implode('`,`', $class::$schema['fields']) . '` FROM ' . $class::$schema['table'] . ' ';
			$query .= static::_queryfier(current($arguments));

			$handler = static::$db->query($query);

			if($handler) {
				return new $class(array('handler' => $handler, 'method' => $method));
			}
			return new $class();
		}

		public static function execute($query, $params = array(), $single = false)
		{
			$class = get_called_class();

			try {
				$sth = static::$db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute($params);

				if($sth) {
					return new $class(array('handler' => $sth, 'method' => ($single ? 'fetch' : 'fetchAll')));
				}
				return new $class();

			} catch(Exception $e) {
				d($e->getMessage() . ' ----- ' . $query . ' ---- ' . $class); die();
			}
		}

		public static function first($arguments = null)
		{
			$class  = get_called_class();

			// Query
			$query  = 'SELECT * FROM ' . $class::$schema['table'];
			$query .= static::_queryfier($arguments);

			try {
				$handler = static::$db->query($query);
				if($handler) {
					$handler->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $class);
					$result = $handler->fetch();
					if($result) {
						return $result;
					}
				}
			} catch(Exception $e) {
				d($e->getMessage() . ' ----- ' . $query . ' ---- ' . $class); die();
			}

			return array();
		}

		public static function create($params = array())
		{
			$class = get_called_class();
			$class = new $class();
			if($params) {
				foreach($params as $field => $value) {
					$class->$field = $value;
				}
			}

			return $class;
		}

		public static function insertBatch($values)
		{
			$insertValues = array();
			$class        = get_called_class();

			foreach($values as $data) {
				$questionMarks[] = '('  . static::placeholders('?', sizeof($data)) . ')';
				$insertValues = array_merge($insertValues, array_values($data));
			}

			$fields = array_slice($class::$schema['fields'], 1);

			$sql = "INSERT INTO " . $class::$schema['table'] .  " (" . implode(',', $fields ) . ") VALUES " . implode(',', $questionMarks);
			$stmt = static::$db->prepare ($sql);
			try {
				$stmt->execute($insertValues);
			} catch (PDOException $e){
				echo $sql;
				echo $e->getMessage();
			}
		}

		public static function export()
		{
			$class = get_called_class();
			$data = $class::all()->toArray();

			$query = 'INSERT INTO ' . $class::$schema['table'] . ' (`' . implode('`,`', $class::$schema['fields']) . '`) VALUES ';
			if($data) {
				foreach($data as $dat) {
					$query .= '("' . implode('","', $dat) . '"),';
				}
			}

			echo substr($query, 0, -1);
		}

		public static function delete($arguments = null)
		{
			$class  = get_called_class();

			// Query
			$query  = 'UPDATE ' . $class::$schema['table'] . ' SET status = 0';
			$query .= static::_queryfier($arguments);

			try {
				return static::$db->query($query);
			} catch(Exception $e) {
				d($e->getMessage() . ' ----- ' . $query . ' ---- ' . $class); die();
			}

			return false;
		}

		public static function remove($arguments = null)
		{
			$class  = get_called_class();

			// Query
			$query  = 'DELETE FROM ' . $class::$schema['table'];
			$query .= static::_queryfier($arguments);

			try {
				return static::$db->query($query);
			} catch(Exception $e) {
				d($e->getMessage() . ' ----- ' . $query . ' ---- ' . $class); die();
			}

			return false;
		}

		/**
		 * Retrieve values from pdo transform them to object or array of objects
		 * Allowed methods rows(), column('id'), count()
		 *
		 * @param bool $keys
		 *
		 * @return array
		 */
		public function __call($name, $arguments = array())
		{
			$class          = get_called_class();
			$fetchMode      = false;
			$fetchParameter = false;

			switch($name) {
				case 'toArray':
					$method         = $this->settings['method'];
					$fetchMode      = PDO::FETCH_ASSOC;
					$fetchParameter = false;

					// Apply directly to object
					if( !$this->settings) {
						return get_object_vars($this);
					}
					break;

				case 'group':
					$method         = $this->settings['method'];
					$fetchMode      = PDO::FETCH_GROUP | PDO::FETCH_ASSOC;
					$fetchParameter = false;

					// Apply directly to object
					if( !$this->settings) {
						return get_object_vars($this);
					}
					break;

				case 'rows':
					$method         = $this->settings['method'];
					$fetchMode      = PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE;
					$fetchParameter = $class;
					break;

				case 'exists':
					$method         = 'fetch';
					$fetchMode      = PDO::FETCH_ASSOC;
					$fetchParameter = false;
					break;

				case 'column':
					$method         = $this->settings['method'];
					$fetchMode      = PDO::FETCH_COLUMN;
					$fetchParameter = array_search($arguments[0], $class::$schema['fields']);
					break;

				case 'count':
					$method = 'rowCount';
					break;

				default:
					die('Method not allowed in model!');
					return;
					break;
			}

			if(isset($this->settings) && $this->settings['handler']) {

				// Fetch mode
				if($fetchMode) {
					if($fetchParameter) {
						$results = $this->settings['handler']->$method($fetchMode, $fetchParameter);
					} else {
						$results = $this->settings['handler']->$method($fetchMode);
					}
				} else {
					$results = $this->settings['handler']->$method();
				}

				if($results) {
					return $results;
				}
				if( ! $fetchMode) {
					return 0;
				}
				return array();
			}
			if( ! $fetchMode) {
				return 0;
			}
			return array();
		}

		public function save()
		{
			$class = get_called_class();

			// Update
			if($this->id) {
				$query = 'UPDATE ' . $class::$schema['table'] . ' SET ';

				$preparedFields = array();
				foreach($class::$schema['fields'] as $field) {
					switch($field) {
						case 'id': case 'created':
							continue(2);
							break;

						case 'modified':
							$this->$field = date(DATE_TIME);
							break;
					}

					$query .= '`' . $field . '` = ?, ';
					$preparedFields[] = (is_null($this->$field) ? '' : $this->$field );
				}
				$query = substr($query, 0, -2);
				$query .= ' WHERE id = ' . $this->id;

				try {
					$sth = static::$db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute($preparedFields);
				} catch(Exception $e) {
					d($e->getMessage() . ' ----- ' . $query . ' ---- ' . $class); die();
				}

				
			// Insert
			} else {
				$query = 'INSERT INTO ' . $class::$schema['table'] . ' SET ';

				foreach($class::$schema['fields'] as $field) {
					switch($field) {
						case 'id':
							continue(2);
							break;

						case 'created': case 'modified':
							$this->$field = date(DATE_TIME);
							break;
					}

					$query .= '`' . $field . '` = ?, ';
					$preparedFields[] = (is_null($this->$field) ? '' : $this->$field );
				}
				$query = substr($query, 0, -2);

				try {
					$sth = static::$db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute($preparedFields);

				} catch(Exception $e) {
					d($e->getMessage() . ' ----- ' . $query . ' ---- ' . $class); die();
				}

				$this->id = static::$db->lastInsertId();
			}
			return $this;
		}

		public static function placeholders($text, $count=0, $separator=","){
			$result = array();
			if($count > 0){
				for($x=0; $x<$count; $x++){
					$result[] = $text;
				}
			}

			return implode($separator, $result);
		}
	}