<?
namespace oxygen\scheme_generator;

    use oxygen\controller\Oxygen_Controller;
    use oxygen\field\Oxygen_Field;

    class Oxygen_SchemeGenerator extends Oxygen_Controller{
        public $yml = null;
        public function __toString(){
            return 'Scheme generator';
        }

        public function __complete(){
            $this->scope->lib->load('yaml-php/lib/sfYaml.php');
            $_file = $this->scope->loader->pathFor($this->model, false, false);
            $parts = explode('/', $_file);
            $parts[count($parts)-1] = 'schema.yml';
            $file = implode('/', $parts);
            $this->yml = sfYaml::load($file);
            $this->field_types = Oxygen_Field::getFieldTypes();
        }

        public function rpc_saveSchema($args){
            $schema = json_decode(json_encode($args->schema), true);
            ksort($schema['classes']);
            $yml = sfYaml::dump($schema, 4);
            $app = (string)$this->model;
            $_file = $this->scope->loader->pathFor($app, false, false);
            $parts = explode('/', $_file);
            $parts[count($parts)-1] = 'schema.yml';
            $file = implode('/', $parts);
            file_put_contents($file, $yml);
            
            return $this->scope->APP->generateClasses();
        }

        public function rpc_buildEntityFromDB($args){
            $db = strtolower(preg_replace("/[^_a-z0-9]+/i", "", $args->db));
            $table = strtolower(preg_replace("/[^_a-z0-9]+/i", "", $args->table));
            return $this->embed_entity($args->db, $args->table);
        }

        public function getTableBones($dbName, $tableName){
            $bones = array();
            $types = array(
                'int'=>'integer',
                'tinyint'=>'integer',
                'smallint'=>'integer',
                'mediumint'=>'integer',
                'bigint'=>'integer',
                'bit'=>'integer',

                'float'=>'double',
                'double'=>'double',
                'decimal'=>'double',

                'char'=>'string',
                'varchar'=>'string',
                'text'=>'text',
                'tinytext'=>'text',
                'mediumtext'=>'text',
                'longtext'=>'text',

                'set'=>'set'
            );
            $columns = $this->scope->connection->runQuery("
                show columns in ".$dbName.".".$tableName."
            ", array(), "Field");
            foreach($columns as $column){
                $type = preg_replace("/^([a-z]+).*/", "$1", $column['Type']);
                $type = isset($types[$type]) ? $types[$type] : 'string';
                if(preg_match("/^dt_/i", $column['Field'])){
                    $type = 'unixtime';
                }
                $bones['fields'][$column['Field']] = array();
                $bones['fields'][$column['Field']]['type'] = $type;
                if($column['Key'] == 'PRI'){
                    $bones['key'] = $column['Field'];
                }
            }

            $tableNameParts = explode('_', $tableName);
            $className = array();
            foreach($tableNameParts as $part){
                $className[] = ucfirst($part);
            }
            $bones['table_name'] = $tableName;
            $bones['class'] = implode('', $className);

            return $bones;
        }

        public function getRelations($dbName, $tableName){
            $relations = array();
            $constraints = $this->scope->connection->runQuery("select
                concat_ws('@', constraint_name, constraint_schema) as id,
                constraint_schema `database`,
                table_name,
                referenced_table_name,
                column_name,
                referenced_column_name,
                constraint_name name
            from
                information_schema.key_column_usage
            where
                referenced_table_name is not null
                and constraint_schema = '".$dbName."'
                and table_name = '".$tableName."'
            ", array(), "id");

            foreach($constraints as $constraint){
                $relations[$constraint['id']] = array();
                $fieldParts = explode(':', $constraint['name']);

                // host class
                $relations[$constraint['id']]['host']['class'] = '';
                $tableNameParts = explode('_', $constraint['table_name']);
                foreach($tableNameParts as $part){
                    $relations[$constraint['id']]['host']['class'] .= ucfirst($part);
                }
                $relations[$constraint['id']]['host']['column'] = $constraint['column_name'];
                
                $relations[$constraint['id']]['host']['field'] = $fieldParts[0];

                // guest class
                $relations[$constraint['id']]['guest']['class'] = '';
                $tableNameParts = explode('_', $constraint['referenced_table_name']);
                foreach($tableNameParts as $part){
                    $relations[$constraint['id']]['guest']['class'] .= ucfirst($part);
                }
                $relations[$constraint['id']]['guest']['column'] = $constraint['referenced_column_name'];
                $relations[$constraint['id']]['guest']['field'] = 'rev_'.$constraint['table_name'];
            }
            return $relations;
        }
    }