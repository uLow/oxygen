<?
namespace oxygen\sql\connection\mysql;

use oxygen\scope\Scope;
use oxygen\sql\connection\Connection;
/**
 * show off @method
 * @property \oxygen\scope\Scope scope
 * @property \oxygen\sql\builder\Builder builder
 * @method \oxygen\sql\connection\Connection Connection()
 * @method \oxygen\sql\database\Database Database()
 * @method \oxygen\sql\table\Table Table()
 * @method \oxygen\sql\columns\Columns Columns()
 * @method \oxygen\sql\column\Column Column()
 * @method \oxygen\sql\key\Key Key()
 * @method \oxygen\sql\keys\Keys Keys()
 * @method \oxygen\sql\data_set\DataSet DataSet()
 * @method \oxygen\sql\builder\Builder Builder()
 * @method \oxygen\sql\result_set\ResultSet ResultSet()
 * @method \oxygen\sql\result_set\iterator\Iterator DataIterator()
 * @method \oxygen\entity\Entity Row()
 */
class Mysql extends Connection
{

    const CENSORED_PASSWORD = '******';
    private static $intents = array(
        'select' => true,
        'insert' => true,
        'delete' => true,
        'update' => true
    );
    private static $implementations = array(
        'Connection' => 'oxygen\\sql\\connection\\Connection',
        'Database' => 'oxygen\\sql\\database\\Database',
        'Table' => 'oxygen\\sql\\table\\Table',
        'Columns' => 'oxygen\\sql\\columns\\Columns',
        'Column' => 'oxygen\\sql\\column\\Column',
        'Key' => 'oxygen\\sql\\key\\Key',
        'Keys' => 'oxygen\\sql\\keys\\Keys',
        'DataSet' => 'oxygen\\sql\\data_set\\DataSet',
        'Builder' => 'oxygen\\sql\\builder\\Builder',
        'ResultSet' => 'oxygen\\sql\\result_set\\ResultSet',
        'DataIterator' => 'oxygen\\sql\\result_set\\iterator\\Iterator',
        'Row' => 'oxygen\\entity\\Entity'
    );
    public $builder = null;
    private $link = null;
    private $initDbCallback = null;
    private $refreshSchemata = false;
    /* @var \oxygen\cache\file\File $cache */
    private $cache = null;
    private $policyCache = array();
    private $policyCallback = null;
    private $lastQuery = "";

    public function __construct($config, $refreshSchemata = false)
    {
        parent::__construct($config);
        $this->model['databases'] = array();
        $this->refreshSchemata = $refreshSchemata;
    }

    public function __complete()
    {
        $this->link = mysqli_connect(
            $this->model['host'],
            $this->model['user'],
            $this->model['pass']
        );
        //echo mysqli_error($this->link);
        $this->model['pass'] = self::CENSORED_PASSWORD;
        $this->model['databases'] = array();
        $this->__assert($this->link, mysqli_error($this->link));
        $this->cache = $this->scope->cache;
        $this->scope->registerAll(self::$implementations);
        $this->scope->connection = $this;
        $this->builder = $this->scope->Builder();
        $this->rawQuery('set names utf8');
    }

    public function rawQuery($sql)
    {
        $this->__assert(
            $result = mysqli_query($this->link, $sql),
            mysqli_error($this->link)
        );
        $this->lastQuery = $sql;
        return $result;
    }

    /**
     * @param \oxygen\controller\routes\Routes[] $x
     */
    public function configure($x)
    {
        $this->reflectDatabases($this->refreshSchemata);
        $x['{database:url}']->Database($this->model['databases']);
    }

    public function reflectDatabases($refresh)
    {
        //if not forced to refresh schemata - exclude already reflected databases
        if ($refresh) {
            $toReflect = $this->model['uses'];
        } else {
            $toReflect = array();
            foreach ($this->model['uses'] as $db) {
                $data = $this->getReflectedDb($db);
                if ($data === false) {
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
        while ($row = mysqli_fetch_assoc($columns)) {
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
        $path = array('database' => 'tables', 'table' => 'keys', 'key' => 'columns', 'ordinal' => '*');
        while ($row = mysqli_fetch_assoc($keys)) {
            $this->structurize($row, $path, $this->model['databases']);
        }
        mysqli_free_result($keys);

        foreach ($toReflect as $db) {
            $this->cacheReflectedDb($db, $this->model['databases'][$db]);
        }

        $tid = 0;
        foreach ($this->model['databases'] as &$db) {
            foreach ($db['tables'] as &$table) {
                $table['id'] = $tid++;
            }
        }

    }

    private function getReflectedDb($name)
    {
        return $this->cache->deserialize(
            $this->getDbKey($name),
            false
        );
    }

    private function getDbKey($dbName)
    {
        return "mysql://{$this->model['user']}@{$this->model['host']}/$dbName";
    }

    private function structurize($row, $path, &$root)
    {
        $prevProp = 'connection';
        $prevObj = &$this->model;
        $x = &$root;
        foreach ($path as $prop => $collection) {
            $key = $row[$prop];
            unset($row[$prop]);
            if (!isset($x[$key])) {
                if ($collection === '*') {
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

    public function cacheReflectedDb($name, $data)
    {
        $this->cache->serialize(
            $this->getDbKey($name),
            $data
        );
    }

    public function __toString()
    {
        return $this->model['user'] . '@' . $this->model['host'];
    }

    public function getIcon()
    {
        return 'database_gear';
    }

    public function getPolicy($table)
    {
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

    // function safeName($name) relies on the assumption
    // that nobody will use a dot (.) within column names.
    // Even in escaped form. Also we assume that there are
    // no escaped leading and trailing spaces.
    // (If it's the case for you - my regrets!)
    // So we will treat any dots in names as dots in qualified names.
    // In case if $name is an array, function produces coma-separated
    // parentheses enclosed list of safeNames of keys in this array
    // this is useful when dealing with inserts and replaces

    public function setPolicyCallback($callback, $method = null)
    {
        $this->__assert(
            $this->policyCallback === null,
            "Can't set policyCallback twice"
        );
        $this->policyCallback = ($method === null)
            ? $callback
            : array($callback, $method);
    }

    public function fetch_assoc($res)
    {
        return mysqli_fetch_assoc($res);
    }

    /**
     * @param \mysqli_result $res
     * @return void
     */
    public function free_result($res)
    {
        mysqli_free_result($res);
    }

    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    public function runQuery($sql, $params = array(), $key = false, $wrapper = false, $method = false)
    {
        $sql = trim($sql);
        $wrapper = $wrapper === false
            ? false
            : ($method === false
                ? $wrapper
                : array($wrapper, $method));

        $this->__assert(
            preg_match("/^(valueof|create|drop|replace|get|select|insert|update|delete|show)/i", $sql, $match),
            'Unknown sql-query type'
        );
        $type = strtolower($match[1]);


        //$sql = preg_replace('/([{<])([A-Za-z0-9_]*?)([>}])/e',
        $sql = preg_replace_callback(
            '/({%|{|<)([A-Za-z0-9_]+?)(:int|:str|:wc)(%}|}|>)/',
            function ($m) use ($params) {
                return $this->processParams($m[1], $m[2], $m[3], $m[4], $params);
            },
            //"\$this->processParams('\\1', '\\2', '\\3', '\\4', \$params)",
            $sql
        );
        /*
        "\$this->{'\\1' === '{' ? 'safeValue' : 'safeName' }(\$params[
            '\\1' === '{' ? '\\2' : '<\\2>'], '')",$sql);*/

        if ($type == 'select') return $this->scope->ResultSet($sql, $key, $wrapper, $this);
        if ($type == 'show') return $this->scope->ResultSet($sql, $key, $wrapper, $this);
        if ($type == 'valueof') {
            $sql = preg_replace("/^valueof/i", "select", $sql);
            $res = $this->rawQuery($sql);
            $row = mysqli_fetch_row($res);
            mysqli_free_result($res);
            if (!$row) return false;
            return $row[0];
        }

        if ($type == 'get') {
            $sql = preg_replace("/^get/i", "select", $sql);
            $res = $this->rawQuery($sql);
            $obj = mysqli_fetch_assoc($res);
            mysqli_free_result($res);
            if (!$obj) return false;
            return $this->wrapData($obj, $wrapper);
        }

        $this->rawQuery($sql);

        switch ($type) {
            case "insert":
                return mysqli_insert_id($this->link);
            case "replace":
                return mysqli_insert_id($this->link);
            case "update":
                return mysqli_affected_rows($this->link);
            case "delete":
                return mysqli_affected_rows($this->link);
            default:
                return mysqli_affected_rows($this->link);
        }
    }

    public function processParams($open, $name, $type, $close, $params)
    {
        //$name = explode(':', $name);
        if (strlen($type) > 0) {
            $type = substr($type, 1);
        } else {
            $type = 'str';
        }

        if ($open == '<' and $close == '>') {
            return $this->safeName($open . $params[$name] . $close);
        } else {
            switch ($type) {
                case 'wc':
                    $likeCond = '';
                    if ($open == '{%') {
                        $likeCond = '%,';
                    } else {
                        $likeCond = ',';
                    }

                    if ($close == '%}') {
                        $likeCond = $likeCond . '%';
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

    public function safeName($name)
    {
        if (is_array($name)) {
            $res = '';
            foreach ($name as $key => $value) {
                $res .= $res === '' ? '(' : ',';
                $res .= $this->safeName($key);
            }
            $res .= $res === '' ? '()' : ')';
            return $res;
        } else if (!preg_match('/^[A-Z_][\.A-Z0-9_]*$/i', $name)) {
            $pieces = explode('.', $name);
            foreach ($pieces as $i => $piece) {
                $pieces[$i] = '`' . str_replace('`', '``', $piece) . '`';
            }
            return implode('.', $pieces);
        } else {
            return $name;
        }
    }

    public function safeValue($value, $type = 'str', $likeCond = false)
    {
        if (is_array($value)) {
            $res = '';
            foreach ($value as $k => $v) {
                $res .= $res === '' ? '(' : ',';
                $res .= $this->safeValue($v);
            }
            $res .= $res === '' ? '()' : ')';
            return $res;
        } else {
            if ($likeCond !== false) {
                $like = explode(',', $likeCond);
                $value = preg_replace('/%/', '\%', $value);
                $value = $like[0] . $value . $like[1];
            }
            if ($type == 'int') {
                return (int)(string)$value;
            } else {
                return '\'' . mysqli_real_escape_string($this->link, $value) . '\'';
            }
        }
    }

    public function wrapData($data, $wrapper = false)
    {
        if ($wrapper === false) {
            return $data;
        } else if ($wrapper === true) {
            return (object)$data;
        } else {
            return call_user_func($wrapper, $data);
        }
    }

    public function formatParams($sql, $params = array())
    {
        return preg_replace_callback(
            '/({%|{|<)([A-Za-z0-9_]+?)(:int|:str|:wc)(%}|}|>)/',
            function ($m) use ($params) {
                return $this->processParams($m[1], $m[2], $m[3], $m[4], $params);
            },
            $sql
        );
    }

    public function lastInsertId()
    {
        return mysqli_insert_id($this->link);
    }

    public function lastAffectedRows()
    {
        return mysqli_affected_rows($this->link);
    }

    public function paramQueryArray($sql, $params = array(), $key = false)
    {
        return $this->resultToArray($this->paramQuery($sql, $params), $key);
    }

    public function resultToArray($res, $key = false)
    {
        $array = array();
        if ($key === false) {
            while ($row = mysqli_fetch_assoc($res)) {
                $array[] = $row;
            }
        } else if ($row = mysqli_fetch_assoc($res)) {
            $this->__assert(
                isset($row[$key]),
                'There is no key named {0}',
                $key
            );
            $array[$row[$key]] = $row;
            while ($row = mysqli_fetch_assoc($res)) {
                $array[$row[$key]] = $row;
            }
        }
        return $array;
    }

    public function paramQuery($sql, $params = array())
    {
        return $this->rawQuery($this->formatQuery($sql, $params));
    }

    public function formatQuery($sql, $params = array())
    {
        return preg_replace_callback(
            '/([{<])([A-Za-z0-9_]*?)([>}])/',
            function ($m) use ($params) {
                if ($m[1] === '{') {
                    return mysqli_real_escape_string($params[$m[2]], $this->link);
                } else {
                    return $params['<' . $m[2] . '>'];
                }
            },
            $sql
        );
    }

    public function rawQueryArray($sql, $key = false)
    {
        return $this->resultToArray($this->rawQuery($sql), $key);
    }

    public function startTransaction()
    {
        mysqli_autocommit($this->link, false);
    }

    public function commit()
    {
        if (!mysqli_commit($this->link)) {
            mysqli_autocommit($this->link, true);
            throw new \Exception("Transaction commit failed");
        }
    }

    public function rollback()
    {
        mysqli_rollback($this->link);
        mysqli_autocommit($this->link, true);
    }
}
