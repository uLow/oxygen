<?
namespace oxygen\sql\data_set;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use oxygen\object\Object;

class DataSet extends Object
    implements
    IteratorAggregate,
    ArrayAccess,
    Countable
{

    const MAX_ROWS = 1000000;
    const MASTER_ALIAS = '_';
    const EMPTY_FILTER = '(1=1)';
    private static $defaults = array(
        'select' => false,
        'get' => false,
        'from' => false,
        'where' => false,
        'order' => false,
        'group' => false,
        'having' => true,
        'limit' => false,
        'offset' => false,
        'keys' => false
    );
    /* @var \oxygen\sql\connection\Connection $connection */
    public $connection = null;
    /* @var \oxygen\sql\builder\Builder $builder */
    public $builder = null;
    public $iterationKey = false;
    public $mainAlias = '';
    public $sql = array();
    public $meta = array();

    public function __construct($meta)
    {
//			if($meta['from'] === null){
//				echo '<pre>';
//				die(Dumper::dump(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0),5, true));
//			}
        if ($meta instanceof DataSet) {
            $meta = $meta->meta;
        }
        $this->mainAlias = key($meta['from']);
        $this->meta = array_merge(self::$defaults, $meta);
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function count()
    {
        $res = $this->connection->rawQueryArray($this->sql['count']);
        return $res[0]['count'];
    }

    public function getColumnNames()
    {
    }

    public function getKeyNames()
    {
    }

    public function getRouter($pattern)
    {
        return $this->scope->Router($pattern, $this);
    }

    public function toOptions()
    {
        foreach ($this as $key => $value) {
            $this->flash($key);
            $result[$key] = $value->getData();
        }
        return $result;
    }

    public function offsetGet($offset)
    {
        if (!is_array($offset)) {
            $ik = $this->getIterationKey();
            if (count($ik) !== 1) {
                throw $this->scope->Exception('Can not obtain an object using scalar key (' . print_r($ik, 1) . ')');
            } else {
                $where = array();
                $where[reset($ik)] = $offset;
            }
        } else {
            $where = $offset;
        }
        $meta = $this->builder->addWhere($this->meta, $where);
        $sql = $this->builder->buildSql($meta, 'select');
        $res = $this->connection->rawQueryArray($sql);
        if (count($res) === 0) {
            return null;
            /*$x = json_encode($offset);
            throw $this->scope->Exception("Index {$x} is out of bounds");*/
        }
        return $this->makeRow($res[0]);
    }

    public function getIterationKey()
    {
        if ($this->iterationKey === false) {
            $this->iterationKey = $this->meta['keys'][0];
        }
        return $this->iterationKey;
    }

    public function makeRow($data)
    {
        return $this->scope->Row($this, $data);
    }

    public function offsetSet($offset, $value)
    {
        throw $this->scope->Exception('Update via DataSet is not implemented yet');
    }

    public function offsetExists($offset)
    {
    }

    public function offsetUnset($offset)
    {
        throw $this->scope->Exception('Delete via DataSet is not implemented yet');
    }

    /**
     * @param $condition
     * @return DataSet
     */
    public function where($condition)
    {
        return $this->scope->DataSet($this->builder->addWhere($this->meta, $condition));
    }

    /**
     * @param $order
     * @return DataSet
     */
    public function order($order)
    {
        return $this->scope->DataSet($this->builder->addOrderBy($this->meta, $order));
    }

    /**
     * @param $group
     * @return DataSet
     */
    public function group($group)
    {
        return $this->scope->DataSet($this->builder->addGroupBy($this->meta, $group));
    }

    /**
     * @param $order
     * @return DataSet
     */
    public function orderSelfish($order)
    {
        return $this->scope->DataSet($this->builder->replaceOrderBy($this->meta, $order));
    }

    public function update($set, $where = array())
    {
        $meta = $this->builder->addWhere($this->meta, $where);
        $meta['set'] = $set;
        $sql = $this->builder->buildSql($meta, 'update');
        return $sql;
    }

    public function insert($data)
    {
        $meta['set'] = $data;
        return $this->builder->buildSql($meta, 'insert');
    }

    public function adjustAutoIncrement(&$data)
    {


    }

    public function slice($offset, $limit)
    {
        return $this->scope->DataSet($this->builder->addSlice($this->meta, $offset, $limit));
    }

    public function __complete()
    {
        $this->connection = $this->scope->connection;
        $this->builder = $this->connection->builder;
        $this->sql['select'] = $this->builder->buildSql($this->meta, 'select');
        $this->sql['count'] = $this->builder->buildSql($this->meta, 'select', true);
    }

    public function getIterator()
    {
        return $this->scope->DataIterator(
            $this->sql['select'],
            $this->getIterationKey(),
            array($this, 'makeRow'),
            $this->scope->connection
        );
    }

    public function first()
    {
        /*foreach ($this->slice(0,1) as $k => $v) {
            return $v;
        }
        return null;*/
        foreach ($this as $v) {
            return $v;
        }
        return null;
    }


}


?>