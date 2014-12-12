<?

	class Oxygen_SQL_Connection_Oracle extends Oxygen_SQL_Connection {

		private $link = null;
        private static $conn = null;
        private $initDbCallback = null;
        private $refreshSchemata = false;
        private $cache = null;
        private $policyCache = array();
        private $policyCallback = null;
        private $lastQuery = "";

        private static $intents = array(
            'select' => true,
            'insert' => true,
            'delete' => true,
            'update' => true
        );

        public $builder = null;

        const CENSORED_PASSWORD = '******';

		private static $implementations = array(
            'Connection'   => 'Oxygen_SQL_Connection',
            'Database'     => 'Oxygen_SQL_Database',
            'Table'        => 'Oxygen_SQL_Table',
            'Columns'      => 'Oxygen_SQL_Columns',
            'Column'       => 'Oxygen_SQL_Column',
            'Key'          => 'Oxygen_SQL_Key',
            'Keys'         => 'Oxygen_SQL_Keys',
            'DataSet'      => 'Oxygen_SQL_DataSet',
            'Builder'      => 'Oxygen_SQL_Builder',
            'ResultSet'    => 'Oxygen_SQL_ResultSet',
            'DataIterator' => 'Oxygen_SQL_ResultSet_Iterator',
            'Row'          => 'Oxygen_Entity'
        );

        public function __construct($config, $refreshSchemata = false) {
            parent::__construct($config);
            $this->model['databases'] = array();
            $this->refreshSchemata = false;//true;//false; //$refreshSchemata;
        }

        public function __complete() {
            //$this->link
            self::$conn = oci_connect(
                $this->model['user'],
                $this->model['pass'],
                $this->model['host'].'/'.$this->model['host-schema'],
                'UTF8'
            );
            //$this->model['pass'] = self::CENSORED_PASSWORD;
            //$database = explode('/', $this->model['host']);
            $this->model['databases'] = array();
            //$this->__assert($this->link, oci_error());
            $this->__assert(self::$conn, oci_error());
            $this->cache = $this->scope->cache;
            $this->scope->registerAll(self::$implementations);
            //$this->scope->oracle = $this;
            $this->builder = $this->scope->Builder();
        }

        public function configure($x) {
            $this->reflectDatabases($this->refreshSchemata);
            $x['{database:url}']->Database($this->model['databases']);
        }

        public function __toString() {
            return $this->model['user'] . '@' . $this->model['host'];
        }

        public function getIcon() {
            return 'database_gear';
        }

        public function getPolicy($table) {
            if ($this->policyCallback !== null) {
                $fullName = $table['database']['name'] . '.' . $table['name'];
                if (isset($this->policyCache[$fullName])) return $this->policyCache[$fullName];
                $policy = array();
                foreach (self::$intents as $intent => $default) {
                    $policy[$intent] = call_user_func(
                        $this->policyCallback,
                        $fullName,
                        $table,
                        $intent,
                        $default
                    );
                }
                return $this->policyCache[$fullName] = $policy;
            } else {
                return self::$intents;
            }
        }

        public function setPolicyCallback($callback, $method = null) {
            $this->__assert(
                $this->policyCallback === null,
                'Can\'t set policyCallback twice'
            );
            $this->policyCallback = ($method === null)
                ? $callback
                : array($callback, $method)
            ;
        }
		
		public function fetch_assoc($res){
			return oci_fetch_assoc($res);
		}
		
		public function free_result($res){
			return oci_free_statement($res);
		}

		public function rawQuery($sql) {
            //die(var_dump($this->link));
            //die(var_dump(self::$_lll));
           // $result = oci_parse($this->link, $sql);
            //Oxygen_Logger::log(false, $sql, false);

            $result = oci_parse(self::$conn, $sql);
            oci_execute($result);

			$this->__assert(
				$result,
                oci_error(self::$conn)
				//oci_error($this->link)
			);

            $this->lastQuery = $sql;

			return $result;
		}

        public function getLastQuery(){
            return $this->lastQuery;
        }

        public function wrapData($data, $wrapper = false) {
            if ($wrapper === false) {
                return $data;
            } else if ($wrapper === true) {
                return (object)$data;
            } else {
                return call_user_func($wrapper, $data);
            }
        }

        // function safeName($name) relies on the assumption
        // that nobody will use a dot (.) within column names.
        // Even in escaped form. Also we assume that there are
        // no escaped leading and trailing spaces.
        // (If it's the case for you - my regrets!)
        // So we will treat any dots in names as dots in qualified names.
        // In case if $name is an array, function produces coma-separated
        // parentheses enclosed list of safeNames of keys in this array
        // this is useful when dealing with inserts and replaces
        public function safeName($name) {
            if(is_array($name)) {
                $res = '';
                foreach ($name as $key => $value) {
                    $res .= $res === '' ? '(' : ',' ;
                    $res .= $this->safeName($key);
                }
                $res .= $res === '' ? '()' : ')' ;
                return $res;
            } else if(!preg_match('/^[A-Z_][\.A-Z0-9_]*$/i', $name)){
                $pieces = explode('.', $name);
                foreach($pieces as $i => $piece) {
                    $pieces[$i] = '`'. str_replace('`', '``', $piece) . '`';
                }
                return implode('.', $pieces);
            } else {
                return $name;
            }
        }


        public function safeValue($value, $type = 'str', $likeCond = false) {
            if(is_array($value)) {
                $res = '';
                foreach ($value as $key => $value) {
                    $res .= $res === '' ? '(' : ',' ;
                    $res .= $this->safeValue($value);
                }
                $res .= $res === '' ? '()' : ')' ;
                return $res;
            } else {
				if($likeCond!==false){
					$like = explode(',', $likeCond);
					$value = preg_replace('/%/', '\%', $value);
					$value = $like[0].$value.$like[1];
				}
				if($type == 'int'){
					return (int)(string)$value;
				}else{
					return '\'' . str_replace("'", "''", $value) . '\'';
				}
            }
        }
		
		public function processParams($open, $name, $type, $close, $params){
			//$name = explode(':', $name);
			if(strlen($type)>0){
				$type = substr($type, 1);
			}else{
				$type = 'str';
			}
			
			if($open == '<' and $close == '>'){
				return $this->safeName($open.$params[$name].$close);
			}else{
				switch($type){
					case 'wc': 
						$likeCond = '';
						if($open=='{%'){
							$likeCond = '%,';
						}else{
							$likeCond = ',';
						}
						
						if($close=='%}'){
							$likeCond = $likeCond.'%';
						}
						
						return $this->safeValue($params[$name], $type, $likeCond);
					break;
					
					case 'int': 
						return $this->safeValue($params[$name], $type);
					break;
					
					case 'str': 
					default:
						return $this->safeValue($params[$name]);
					break;
				}
			}
		}
        public function runQuery($sql, $params = array(), $key = false, $wrapper = false, $method = false) {
			$sql = trim($sql);

            $wrapper = $wrapper === false
                ? false
                : ($method === false
                ? $wrapper
                : array($wrapper, $method))
            ;

            $this->__assert(
                preg_match("/^(valueof|create|drop|replace|get|select|insert|update|delete)/i", $sql, $match),
                'Unknown sql-query type'
            );
            $type = strtolower($match[1]);

            ////$sql = preg_replace('/([{<])([A-Za-z0-9_]*?)([>}])/e',
            $sql = preg_replace_callback(
                '/({%|{|<)([A-Za-z0-9_]+?)(:int|:str|:wc)?(%}|}|>)/',
                function($m) use($params){
                    return $this->processParams($m[1], $m[2], $m[3], $m[4], $params);
                },
                $sql
            );
            
            $sql = preg_replace_callback(
                '/\s([._a-z0-9]+)(\snot)?\sin\s?\(\s?(.*)\)/i',
                function($m){
                    $key = $m[1];
                    $not = $m[2];
                    $in = explode(',', $m[3]);
                    if(count($in) > 500){
                        $inList = array();
                        $i = 0;
                        $return = array();
                        foreach($in as $item){
                            $inList[$i][] = $item;
                            if(count($inList[$i]) == 500){
                                $i++;
                            }
                        }
                        foreach($inList as $split){
                            $return[] = $key.' '.$not.' in('.implode(',', $split).')';
                        }
                        return ' ('.implode(' or ', $return).')';
                    }else{
                        return ' '.$key.' '.$not.' in('.implode(',', $in).')';
                    }
                },
                $sql
            );
            /*
                "\$this->{'\\1' === '{' ? 'safeValue' : 'safeName' }(\$params[
                    '\\1' === '{' ? '\\2' : '<\\2>'], '')",$sql);*/

            if($type == 'select') return $this->scope->ResultSet($sql, $key, $wrapper, $this);
            if($type == 'valueof') {
                $sql = preg_replace("/^valueof/i", "select", $sql);
                $res = $this->rawQuery($sql);
                $row = oci_fetch_row($res);
                oci_free_statement($res);
                if(!$row) return false;
                return $row[0];
            }

            if($type == 'get') {
                $sql = preg_replace("/^get/i", "select", $sql);
                $res = $this->rawQuery($sql);
                $obj = oci_fetch_assoc($res);
                oci_free_statement($res);
                if(!$obj) return false;
                return $this->wrapData($obj, $wrapper);
            }

            $statement = $this->rawQuery($sql);

            throw new Exception("ORACLE is READ-ONLY!!!");

            /*

            switch($type){
            case "insert": return oci_num_rows($statement); // be careful! this return affected rows count, not ids as in mysql version. TODO: get last insert id
            case "replace": return oci_num_rows($statement); // be careful! this return affected rows count, not ids as in mysql version. TODO: get last insert id
            case "update": return oci_num_rows($statement);
            case "delete": return oci_num_rows($statement);
            default:
                return oci_num_rows($statement);
            }
            */
        }

        public function formatParams($sql, $params = array()) {
			preg_replace_callback(
                '/({%|{|<)([A-Za-z0-9_]+?)(:int|:str|:wc)?(%}|}|>)/',
                function($m) use($params){
                    return $this->processParams($m[1], $m[2], $m[3], $m[4], $params);
                },
                $sql
            );
        }
		
        public function formatQuery($sql, $params = array()) {
            return preg_replace_callback(
                '/([{<])([A-Za-z0-9_]*?)([>}])/',
                function($m) use($params){
                    if($m[1] === '{'){
                        return str_replace("'", "''", $params[$m[2]]);
                    }else{
                        return $params['<'.$m[2].'>'];
                    }
                },
                $sql
            );
        }

		public function paramQuery($sql, $params = array()) {
            return $this->rawQuery($this->formatQuery($sql, $params));
		}

        public function lastInsertId() {
			throw new Exception('There is no implemented lastInsertId for oracle yet.'); // be careful! 
            //return mysqli_insert_id($this->link);
        }

        public function lastAffectedRows() {
            //return oci_num_rows($this->link);
            return oci_num_rows(self::$conn);
        }

        public function paramQueryArray($sql, $params = array(), $key = false) {
            return $this->resultToArray($this->paramQuery($sql, $params),$key);
        }

        public function rawQueryArray($sql, $key = false) {
            return $this->resultToArray($this->rawQuery($sql),$key);
        }

		public function resultToArray($res, $key = false) {
			$array = array();
			if($key === false) {
				while($row = oci_fetch_assoc($res)) {
					$array[] = $row;
				}
			} else if ($row = oci_fetch_assoc($res)) {
				$this->__assert(
					isset($row[$key]),
					'There is no key named {0}',
					$key
				);
				$array[$row[$key]] = $row;
				while($row = oci_fetch_assoc($res)) {
					$array[$row[$key]] = $row;
				}
			}
			return $array;
		}

        private function getDbKey($dbName) {
            //return "mysql://{$this->model['user']}@{$this->model['host']}/$dbName";
            return "oracle://{$this->model['user']}@{$this->model['host']}/{$this->model['host-schema']}";
        }

        private function getReflectedDb($name) {
            return $this->cache->deserialize(
                $this->getDbKey($name),
                false
            );
        }

        public function cacheReflectedDb($name, $data) {
            $this->cache->serialize(
                $this->getDbKey($name),
                $data
            );
        }

        private function reflectDatabases($refresh) {
			/*throw new Exception('Database reflection is going to be reflected from yaml scheme instead of information schema.');
			*/
            //if not forced to refresh schemata - exclude already reflected databases
            if($refresh) {
                $toReflect = $this->model['uses'];
            } else {
                $toReflect = array();
                foreach($this->model['uses'] as $db) {
                    $data = $this->getReflectedDb($db);
                    if($data === false) {
                        $toReflect[] = $db;
                    } else {
                        $data['connection'] = &$this->model;
                        $this->model['databases'][$db] = $data;
                    }
                }
            }

            if (count($toReflect) === 0) return;

            $list = $this->builder->buildValueList($toReflect);
            $columns = $this->rawQuery("SELECT
                    TABLE_SCHEMA             as `database`,
                    TABLE_NAME               as `table`,
                    COLUMN_NAME              as `column`,
                    ORDINAL_POSITION         as `ordinal`,
                    COLUMN_DEFAULT           as `default`,
                    IS_NULLABLE              as `nullable`,
                    DATA_TYPE                as `type`,
                    CHARACTER_MAXIMUM_LENGTH as `max`,
                    NUMERIC_PRECISION        as `precision`,
                    NUMERIC_SCALE            as `scale`,
                    CHARACTER_SET_NAME       as `charset`,
                    COLLATION_NAME           as `collation`,
                    COLUMN_TYPE              as `def`,
                    EXTRA                    as `extra`
                FROM
                    INFORMATION_SCHEMA.COLUMNS
                WHERE
                    TABLE_SCHEMA IN {$list}
                ORDER BY
                    TABLE_SCHEMA, TABLE_NAME, ORDINAL_POSITION
            ");
            $path = array('database' => 'tables', 'table' => 'columns', 'column' => '*');
            while($row = mysqli_fetch_assoc($columns)){
                $this->structurize($row, $path, $this->model['databases']);
            }
            mysqli_free_result($columns);

            $keys = $this->rawQuery("SELECT
                c.TABLE_SCHEMA                                              as `database`,
                c.TABLE_NAME                                                as `table`,
                c.CONSTRAINT_NAME                                           as `key`,
                case c.CONSTRAINT_TYPE when 'PRIMARY KEY' then 1 else 0 end as `primary`,
                u.COLUMN_NAME                                               as `column`,
                u.ORDINAL_POSITION-1                                        as `ordinal`
            FROM
                INFORMATION_SCHEMA.TABLE_CONSTRAINTS as c
                INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE as u
                    ON c.TABLE_SCHEMA = u.TABLE_SCHEMA
                      AND c.TABLE_NAME = u.TABLE_NAME
                      AND c.CONSTRAINT_NAME = u.CONSTRAINT_NAME
            WHERE
                c.CONSTRAINT_TYPE IN ('UNIQUE','PRIMARY KEY')
                AND c.TABLE_SCHEMA in {$list}
            ORDER BY
                c.TABLE_SCHEMA,
                c.TABLE_NAME,
                `primary` DESC,
                c.CONSTRAINT_NAME,
                u.ORDINAL_POSITION
            ");
            $path = array('database' => 'tables', 'table' => 'keys', 'key' => 'columns', 'ordinal'=>'*');
            while($row = mysqli_fetch_assoc($keys)){
                $this->structurize($row, $path, $this->model['databases']);
            }
            mysqli_free_result($keys);

            foreach($toReflect as $db) {
                $this->cacheReflectedDb($db, $this->model['databases'][$db]);
            }

            $tid = 0;
            foreach($this->model['databases'] as &$db) {
                foreach($db['tables'] as &$table) {
                    $table['id'] = $tid++;
                }
            }
			/**/
        }

        private function structurize($row, $path, &$root) {
            $prevProp = 'connection';
            $prevObj = &$this->model;
            $x = &$root;
            foreach($path as $prop => $collection) {
                $key = $row[$prop];
                unset($row[$prop]);
                if(!isset($x[$key])) {
                    if($collection === '*') {
                        $row[$prevProp] = &$prevObj;
                        $row['name'] = $key;
                        $x[$key] = $row;
                        break;
                    } else {
                        $new = array();
                        $new['name'] = $key;
                        $new[$collection] = array();
                        $new[$prevProp] = &$prevObj;
                        $x[$key] = $new;
                    }
                }
                $prevProp = $prop;
                $x = &$x[$key];
                $prevObj = &$x;
                $x = &$x[$collection];
            }
        }


	}

?>