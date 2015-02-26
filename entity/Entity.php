<?
namespace oxygen\entity;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use oxygen\dumper\Dumper;
use oxygen\object\Object;
use oxygen\scope\Scope;
use oxygen\utils\text\Text;

class Entity extends Object implements ArrayAccess, IteratorAggregate
{

    const MISSING_DATA = 'Part of data is missing with key "{0} for class {1}"';
    const WRONG_ARGUMENT_COUNT = 'Wrong argument count';
    public $database = 'default_database';
    public $table = 'default_table';
    private $original = array();
    private $current = array();
    private $new = false;
    /* @var Entity|\oxygen\sql\data_set\DataSet $owner */
    private $owner = null;
    private $mainAlias = '';

    /**
     * @param Entity|\oxygen\sql\data_set\DataSet $owner
     * @param bool $original
     */
    public function __construct($owner, $original = false)
    {
        if ($original === false) {
            if (!is_object($owner)) {
                $original = $owner;
                $owner = null;
            } else {
                $this->new = true;
                $original = array();
            }
        }
        $this->original = $original;
        $this->current = $original;
        $this->owner = $owner;
        if (is_object($owner)) {
            $this->mainAlias = $owner->mainAlias;
        }
    }

    /**
     * Placeholder for generated method Entity::all()
     */
    public static function all()
    {
    }

    public function getIterator()
    {
        return new ArrayIterator($this->current);
    }

    public function getData()
    {
        return $this->current;
    }

    public function __submit()
    {
        if (!$this->scope) {
            $this->scope = Scope::getRoot();
        }
        $this->__preSubmit();

        if (count($this->original)) {
            // UPDATE EXISTING
            $update = array();
            foreach ($this->current as $key => $value) {
                if ($this->original[$key] != $value) {
                    $update[$key] = $value;
                }
            }
            $where = array();
            foreach ($this->owner->meta['keys'][0] as $c) {
                $where[$c] = $this->original[$c];
            }
            if (count($update) > 0) {
                $this->scope->connection->rawQuery($this->owner->update($update, $where));
                return $this->scope->connection->lastAffectedRows();
            } else {
                return 0;
            }
        } else {
            // IS NEW
            $this->original = $this->current;
            $result = $this->insert($this->current);
            if (is_string($this->__getPrimaryKey())) {
                $this[$this->__getPrimaryKey()] = (string)$result;
                $this[$this->__getPrimaryKey(true)] = (string)$result;
            }
            return $this;
        }
    }

    public function __preSubmit()
    {
    }

    public function insert($data)
    {
        $keys = array_keys($data);
        $values = array_values($data);
        $values = array_map("addslashes", $values);
        $primary_key = preg_replace("/\{([^:]+):.*\}/i", "$1", $this->__getPattern());
        $ret = $this->scope->connection->runQuery("insert into `{$this->database}`.{$this->table} (`" . implode("`,`", $keys) . "`) values ('" . implode("','", $values) . "')");
        return $ret;
    }

    /**
     * Get controller pattern to access records in database table
     * @return string
     */
    public function __getPattern()
    {
        return '{id:int}';
    }

    /**
     * @return string|array
     */
    public function __getPrimaryKey()
    {
        return '';
    }

    public function __remove()
    {
        if (!$this->scope) {
            $this->scope = Scope::getRoot();
        }
        $this->__preRemove();
        $primary_key = $this->__getPrimaryKey();
        $id = $this[$this->__getPrimaryKey()];
        $this->scope->connection->rawQuery("delete from {$this->database}.{$this->table} where {$primary_key}='{$id}'");
    }

    public function __preRemove()
    {

    }

    public function offsetExists($data)
    {
        if (is_string($data)) {
            return isset($this->current[$data]);
        } else if (is_array($data)) {
            foreach ($data as $d) {
                if (!$this->offsetExists($d)) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function offsetUnset($data)
    {

        if (is_string($data)) {
            unset($this->current[$data]);
        } else if (is_array($data)) {
            foreach ($data as $d) {
                $this->offsetUnset($d);
            }
        }
    }

    public function offsetSet($data, $value)
    {
        if (is_string($data)) {
            if ($this->mainAlias !== '') {
                $trial = $this->mainAlias . '.' . $data;
                if (array_key_exists($trial, $this->current)) {
                    $data = $trial;
                }
            }
            $this->current[$data] = $value;
        } else if (is_array($data)) {
            foreach ($data as $k => $d) {
                //if(!array_key_exists($k,$value)) {
                if (!isset($value[$k])) {
                    throw $this->scope->Exception(
                        Text::format(self::MISSING_DATA, $k, get_class($this))
                    );
                }
                $this->offsetSet($d, $value[$k]);
            }
        }
    }

    public function offsetGet($data)
    {
        if (is_string($data) || is_integer($data)) {
            if ($this->mainAlias !== '') {
                $trial = $this->mainAlias . '.' . $data;
                if (array_key_exists($trial, $this->current)) {
                    return $this->current[$trial];
                }
            }
            if (array_key_exists($data, $this->current)) {
                return $this->current[$data];
            }

            throw $this->scope->Exception(
                Text::format(self::MISSING_DATA, $data, get_class($this))
            );

            $result = $this->current[$data];
        } else if (is_array($data)) {
            $result = array();
            foreach ($data as $k => $d) {
                $result[$k] = $this->offsetGet($d);
            }
        }
        return $result;
    }

    public function insertRow($data, $prefix, $table, $raw = false)
    {
        $insertPart = array();
        foreach ($data as $k => $v) {
            $insertPart[] = "`" . addslashes($k) . "`='" . addslashes($v) . "'";
        }
        if ($raw === false) {
            return $this->scope->connection->runQuery("
                    insert into " . addslashes($prefix) . "." . addslashes($table) . " SET
                    " . implode(', ', $insertPart) . "
                ");
        } else {
            return $this->scope->connection->rawQuery("
                    insert into " . addslashes($prefix) . "." . addslashes($table) . " SET
                    " . implode(', ', $insertPart) . "
                ");
        }
    }

    public function dump($depth = 10, $highlight = false)
    {
        return Dumper::dump($this->current, $depth, $highlight);
    }
}


?>