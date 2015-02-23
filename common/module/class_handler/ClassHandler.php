<?
namespace oxygen\common\module\class_handler;
    use Exception;
    use oxygen\object\Object;
    use oxygen\utils\text\Text;

    class ClassHandler extends Object {

        public $name;
        public $yml;
        public $schema;

        public $source;
        public $key;
        public $pattern;
        public $string = '';

        public $fields = array();
        public $relations = array();
        public $readonly = false;

        public function __construct($name, $yml, $schema) {
            $this->name = $name;
            $this->yml = $yml;
            $this->schema = $schema;
            $this->readonly = isset($yml['readonly']) && $yml['readonly']===true;
            $this->string = isset($yml['string']) ? $yml['string'] : '';
            $this->namespace = isset($schema->yml['namespace']) ? $schema->yml['namespace'] : '';
        }

        public function getClassFor($kind, $pluralize = false) {
            return $this->schema->originNamespace . '_' . $kind .'_' .
                ($pluralize ? $this->pluralize($this->name) : $this->name);
        }

        public function getCollectionName() {
            if (isset($this->yml['name'])) return $this->yml['name'];
            else return preg_replace('/([a-z])([A-Z])/','$1 $2',$this->pluralize($this->name));
        }

        public function getTranslateKey(){
            if (isset($this->yml['translate_key'])) return $this->yml['translate_key'];
            else return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $this->pluralize($this->name)));
        }

        public function getIcon() {
            if (isset($this->yml['icon'])) return $this->yml['icon'];
            else return 'plugin';
        }

        public function __complete() {
            try {
                assert('isset($this->yml["source"])');
                assert('isset($this->yml["key"])');
                assert('isset($this->yml["pattern"])');
                $this->source = $this->schema->getSource($this->yml['source']);
                $this->key = $this->yml['key'];
                $this->pattern = $this->yml['pattern'];
            } catch (Exception $e) {
                $this->throw_ClassException($e);
            }
        }

        private static function getWritableDir($base, $dir = '.') {
            if(is_array($dir)) $dir = implode(DIRECTORY_SEPARATOR, $dir);
            assert('is_string($dir)');
            if ($dir !== '.') {
                $next = $base . DIRECTORY_SEPARATOR . $dir;
            } else {
                $next = $base;
            }
            if(!is_writable($next) || !is_dir($next)) {
                self::getWritableDir(dirname($next));
                if(!file_exists($next)) {
                    mkdir($next);
                }
            }
            return $next;
        }     

        private static function inverseJoin($a) {
            $b = array();
            foreach($a as $k=>$v){
                $b[$v]=$k;
            }
            return $b;
        }   

        private static function pluralize($name) {
            return preg_replace(array('/(z|s)$/','/y$/','/ex$/'), array('$1e','ie','ice'), $name).'s';        
        }

        public function resolveClassDependencies() {
            if (isset($this->yml['relations'])) {
                foreach($this->yml['relations'] as $relName => $relDef) {
                    assert('isset($relDef["class"])');
                    assert('isset($relDef["type"])');
                    assert('isset($relDef["join"])');
                    assert('isset($relDef["inverse"])');
                    assert('isset($relDef["inverse"]["name"])');
                    assert('isset($relDef["inverse"]["type"])');
                    $invClass = $this->schema->getClass($relDef['class']);
                    $invName = $relDef['inverse']['name'];
                    $invType = $relDef['inverse']['type'];
                    assert('!isset($invClass->relations[$invName])');
                    $invClass->relations[$invName] = array(
                        'class' => $this,
                        'type' => $invType,
                        'inverse' => array('name'=> $relName, 'type' => $relDef['type']),
                        'join' => self::inverseJoin($relDef['join'])
                    );
                    $relDef['class'] = $invClass;
                    $this->relations[$relName] = $relDef;
                }
            }
        }

        public function initFields() {
            if(isset($this->yml['fields'])) {
                foreach ($this->yml['fields'] as $fieldName => $fieldDef) {
                    assert('isset($fieldDef["type"])');
                    $fieldDef['readonly'] = $this->readonly
                     || (isset($fieldDef['readonly']) && $fieldDef['readonly'] === true)
                     || $fieldDef['type'] === 'collection'
                     || $fieldDef['type'] === 'cross';
                    try {
                        if($fieldDef['type'] === 'collection' 
                            || $fieldDef['type'] === 'cross' 
                            || $fieldDef['type'] === 'object') {
                            $fieldDef['data'] = $this->relations[$fieldName]['join'];
                            $fieldDef['entity-class'] = Text::ns($this->relations[$fieldName]['class']->getClassFor('Entity'));
                        }
                        $field = preg_replace_callback(
                            '/(_|^)([a-z])/', 
                            function($m){
                                return ucfirst($m[2]);
                            },
                            $fieldDef['type']
                        );
                        $this->fields[$fieldName] = $this->scope->{Text::ns('Oxygen_Field_' . $field)}($this, $fieldName, $fieldDef);
                    } catch(Exception $e) {
                        $this->throw_ClassException($e);
                    }
                }
            }
        }

        private function throw_ClassException($e) {
            $this->throw_Exception($e->getMessage() 
                . ' in ' . $this->schema->moduleClassName . '::' . $this->name, 0, $e);
        }

        public function generate() {
            $this->initFields();
            foreach (array('Entity','Form') as $kind) {
                foreach(array('','Base') as $base) {
                    echo "<hr>".$this->name.$base.'<br>';
                    foreach(array(true,false) as $plural) {
                        $name = $this->name;
                        if ($plural) $name = self::pluralize($name);
                        //$className = Text::classToNamespace($this->namespace, $name).'\\'.$name;
                        //$className = $name . $base;
                        echo $kind."::".$name."<br>";
//                        $fileName = $this->scope->loader->pathFor($className, false, false);
                        $fileName = $this->buildCacheClassPath($name, $kind, $base);
                        if($base === '' && file_exists($fileName)) continue;
                        $dir = dirname($fileName);
                        self::getWritableDir($dir);
                        $template = $base === '' ? 'final' : strtolower($kind);
                        if ($plural) $template = self::pluralize($template);
                        $className = ($name . $base);
                        file_put_contents(
                            $fileName,
                            $this->get_($template,array(
                                'className'=>$className,
                                'namespace'=>Text::classToNamespace($this->namespace.'\\'.Text::snakeify($kind),  $name)
                            ))
                        );
                        echo '<font style="color: maroon">'.$fileName.' generated</font><br>';
                        //chmod($fileName, 0777);
                    }
                }
            }
        }

        private function buildCacheClassPath($className, $kind, $base)
        {
            $cachePrefix = '';
            if($base !== ''){
                $cachePrefix = 'cache' . DIRECTORY_SEPARATOR;
            }

            return CURRENT_ROOT_PATH . DIRECTORY_SEPARATOR . $cachePrefix . implode(
                DIRECTORY_SEPARATOR,
                explode(
                    '\\',
                    Text::classToNamespace($this->namespace.'\\'.Text::snakeify($kind),  $className) . '\\' . $className
                )
            ) . $base . '.php';
        }
    }
?>