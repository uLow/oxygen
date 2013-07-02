<?

    class Oxygen_Entity extends Oxygen_Object implements ArrayAccess, IteratorAggregate {

        const MISSING_DATA = 'Part of data is missing with key "{0} for class {1}"';
        const WRONG_ARGUMENT_COUNT = 'Wrong argument count';

        private $original = array();
        private $current = array();
        private $new = false;
        private $owner = null;
        private $mainAlias = '';

        public function getDefaults() {
            return array();
        }

        public function getIterator() {
            return new ArrayIterator($this->current);
        }

        public function getData() {
            return $this->current;
        }

        public function __preSubmit() {
        }

        public function __submit() {
            $conn = $this->scope->connection;
            $this->__preSubmit();
            if(count($this->original)) {
                // UPDATE EXISTING
                $update = array();
                foreach ($this->current as $key => $value) {
                    if($this->original[$key] != $value) {
                        $update[$key] = $value;
                    }
                }
                $where = array();
                foreach($this->owner->meta['keys'][0] as $c) {
                    $where[$c] = $this->original[$c];
                }
                if(count($update)>0){
                    $conn->rawQuery($this->owner->update($update,$where));
                    return $conn->lastAffectedRows();
                }else{
                    return 0;
                }
            } else {
                // IS NEW

                $sql = $this->owner->insert($this->current);
                /*
                $conn->rawQuery();
                $this->original = $this->current;
                return $conn->lastAffectedRows();
                */
                return $sql;
            }
        }

        public function __construct($owner, $original = false) {
            if($original === false) {
                if (!is_object($owner)) {
                    $original = $owner;
                    $owner = null;
                } else {
                    $new = true;
                    $original = array();
                    $current = $this->getDefaults();
                }
            }
            $this->original = $original;
            $this->current = $original;
            $this->owner = $owner;
            if (is_object($owner)) {
                $this->mainAlias = $owner->mainAlias;
            }
        }

        public function offsetExists($data) {
            if(is_string($data)) {
                return isset($this->current[$data]);
            } else if(is_array($data)) {
                foreach($data as $d) {
                    if(!$this->offsetExists($d)) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        public function offsetUnset($data) {

            if(is_string($data)) {
                unset($this->current[$data]);
            } else if(is_array($data)) {
                foreach($data as $d) {
                    $this->offsetUnset($d);
                }
            }
        }

        public function offsetSet($data,$value) {
            if(is_string($data)){
                if ($this->mainAlias !== '') {
                    $trial = $this->mainAlias . '.' . $data;
                    if(array_key_exists($trial,$this->current)){
                        $data = $trial;
                    }
                }
                $this->current[$data] = $value;
            } else if(is_array($data)) {
                foreach($data as $k => $d) {
                    if(!array_key_exists($k,$value)) {
                        throw $this->scope->Exception(
                            Oxygen_Utils_Text::format(self::MISSING_DATA,$k,get_class($this))
                        );
                    }
                    $this->offsetSet($d, $value[$k]);
                }
            }
        }

        public function offsetGet($data) {
            if(is_string($data) || is_integer($data)) {
                if ($this->mainAlias !== '') {
                    $trial = $this->mainAlias . '.' . $data;
                    if(array_key_exists($trial,$this->current)) return $this->current[$trial];
                }
                if(array_key_exists($data,$this->current)) return $this->current[$data];
                throw $this->scope->Exception(
                    Oxygen_Utils_Text::format(self::MISSING_DATA,$data,get_class($this))
                );

                $result = $this->current[$data];
            } else if(is_array($data)) {
                $result = array();
                foreach($data as $k => $d) {
                    $result[$k] = $this->offsetGet($d);
                }
            }
            return $result;
        }

        public function insertRow($data, $prefix, $table, $raw=false)
        {
            $insertPart = array();
            foreach($data as $k=>$v){
                $insertPart[] = "`".addslashes($k)."`='".addslashes($v)."'";
            }
            if($raw === false){
                return $this->scope->connection->runQuery("
                    insert into ".addslashes($prefix).".".addslashes($table)." SET
                    ".implode(', ', $insertPart)."
                ");
            }else{
                return $this->scope->connection->rawQuery("
                    insert into ".addslashes($prefix).".".addslashes($table)." SET
                    ".implode(', ', $insertPart)."
                ");
            }
        }
    }



?>