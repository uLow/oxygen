<?
namespace oxygen\sql\result_set;

    use ArrayAccess;
    use Countable;
    use IteratorAggregate;
    use oxygen\object\Object;

    class ResultSet extends Object
        implements IteratorAggregate, ArrayAccess, Countable
        
    {

        const COUNT_IS_NOT_KNOWN = -1;

        public $sql     = '';
        public $wrapper = false;
        public $key     = false;
        public $conn    = false;
        private $count  = null;

        public function __construct($sql, $key, $wrapper, $conn) {
            $this->wrapper = $wrapper;
            $this->sql     = $sql;
            $this->key     = $key;
            $this->conn    = $conn;
        }

        public function getIterator() {
            $sql = str_replace('{}', '(1=1)', $this->sql);
            return $this->scope->DataIterator(
                $sql,
                $this->key,
                $this->wrapper,
                $this->conn
            );
        }

        public function count() {
            if ($this->count === null) {
                $this->count = $this->conn->runQuery("valueof count(*) from (".str_replace('{}', '(1=1)', $this->sql).") a");
            }
            return $this->count;
        }

        public function formatWhere($key, $val, $type = 'str') {
            if (is_string($key)) {
                return $this->conn->safeName($key) . '=' . $this->conn->safeValue($val, $type);
            } else if (is_array($key)) {
                $cond = '(1 = 1)';
                $single = count($key) === 1;
                foreach ($key as $name => $type) {
                    if (is_integer($name)) {
                        $name = $type;
                        $type = $str;
                    }
                    if ($single && !is_array($value)) {
                        $v = array();
                        $v[$name] = $value;
                        $value = $v;
                    } else {
                        throw new \Exception("Value {$value} not conform for key" . print_r($key, true));
                    }
                    if (!isset($value[$name])) throw new Exception("Part of key {$name} is missing in" . print_r($value, true));
                    $cond .=  ' AND ' . $this->formatWhere($name, $value[$name], $type);
                }
                return $cond;
            } else {
                throw new \Exception("Please define key to access the ResultSet as an array");
            }
        }

        public function offsetGet($offset) {
            $where = $this->formatWhere($this->key, $offset);
            $sql = str_replace('{}', $where, $this->sql);
            $sql = preg_replace('/^select/i','get',$sql);
            return $this->conn->runQuery($sql,array(),$this->key,$this->wrapper);
        }

        public function offsetExists($offset) {
            return $this->offsetGet($offset) !== null;
        }

        public function offsetSet($offset, $key) {
            throw new \Exception("Can't assign to result set");
        }

        public function offsetUnset($offset) {
            throw new \Exception("Can't delete from result set");
        }

		
		public function toArray($owner = false){
			$result = array();
			if($owner === false){
				foreach($this as $k=>$v){
					$result[$k] = $v;
				}
			}else{
				foreach($this as $k=>$v){
					$result[$k] = $owner->scope->Row($owner, $v);
				}
			}
			return $result;
		}
    }
?>