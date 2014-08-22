<?

    class Oxygen_Common_ModelGenerator extends Oxygen_Controller {

        public $namespace = '';

        public function __construct($namespace) {
            parent::__construct($namespace);
        }

        public function applyArgs(&$v, $params) {
            if(is_string($v)) {
                //$v = preg_replace('/{\$([A-Za-z0-9_]+)}/e', "\$params['\\1']", $v);
                $v = preg_replace_callback(
                    '/{\$([A-Za-z0-9_]+)}/', 
                    function($m) use($params){
                        return $params[$m[1]];
                    },
                    $v
                );
            } else if(is_array($v)) {
                foreach($v as $key => &$val) {
                    $this->applyArgs($val, $params);
                }
            }
        }

        public function invert($a) {
            $b = array();
            foreach($a as $k=>$v){
                $b[$v]=$k;
            }
            return $b;
        }

        public function __toString() {
            return 'Models';
        }

        public function getIcon() {
            return 'car';
        }

        public function resolveSource($db,$source){
            $parts = explode('.', $source);
            return $db[$parts[0]].'/'.$parts[1];
        }

        public function rpc_Generate() {
            foreach($this as $m){
                $m->generate();
            }
        }

        public function rpc_RefreshSchema() {

        }

        public static function pluralize($name) {
            if (preg_match('/^(.*)(z|s)$/',$name,$m)) {
                return $m[1].$m[2].'es';
            } else if (preg_match('/^(.*)y$/',$name,$m)) {
                return $m[1].'ies';
            } else if (preg_match('/^(.*)ex/', $name, $m)) {
                return $m[1].'ices';
            } else {
                return $name . 's';
            }
        }

        public function configure($x) {
            $schema = $this->loadYaml('schema.yml');
            $mixins = $schema['mixins'];
            $loader = $this->scope->loader;
            $classes = &$schema['classes'];
            $databases = $schema['databases'];
            $collections = array();
            foreach($classes as $shortClassName => &$classDef) {

                $classDef['source'] = $this->resolveSource($databases,$classDef['source']);
                $classDef['shortClassName'] = $shortClassName;
                $classDef['className'] = array();
                $classDef['classFile'] = array();
                $classDef['classBase'] = array();
                $shortClassNameCollection 
                        = $classDef['shortClassNameCollection'] 
                        = self::pluralize($shortClassName);
                $classDef['classNameCollection'] = array();
                $classDef['classFileCollection'] = array();
                $classDef['classBaseCollection'] = array();

                foreach (array('model','form','page') as $type) {
                    $className = $this->model[$type] . '_' . $shortClassName;
                    $classFile  = $loader->pathFor($className, false, false);
                    $classBase  = $loader->pathFor($className.'_', false, false);
                    $classDef['className'][$type] = $className;
                    $classDef['classFile'][$type] = $classFile;
                    $classDef['classBase'][$type] = $classBase;
                    $classNameCollection = $this->model[$type] . '_' . $shortClassNameCollection;
                    $classFileCollection  = $loader->pathFor($classNameCollection, false, false);
                    $classBaseCollection  = $loader->pathFor($classNameCollection.'_', false, false);
                    $classDef['classNameCollection'][$type] = $classNameCollection;
                    $classDef['classFileCollection'][$type] = $classFileCollection;
                    $classDef['classBaseCollection'][$type] = $classBaseCollection;
                }

                if(isset($classDef['uses'])) {
                    foreach ($classDef['uses'] as $mixinName => $mixinArgs) {
                        $mixin = $mixins[$mixinName];
                        $defaults = $mixin['args'];
                        $args = array_merge($defaults, $mixinArgs);
                        $this->applyArgs($mixin, $args);
                        $mixed = array_merge_recursive($mixin, $classDef);
                        foreach ($mixed as $key => $value) {
                            $classDef[$key] = $value;
                        }
                    }
                }
                $ro = false;
                if(isset($classDef['readonly'])){
                    $ro = $classDef['readonly'];
                } else {
                    $ro = false;
                }
                foreach ($classDef['fields'] as $fieldName => &$fieldDef) {
                    if(!isset($fieldDef['data'])){
                        switch($fieldDef['type']){
                            case 'object':
                                $relation = $classDef['relations'][$fieldName];
                                $fieldDef['data'] = $relation['join'];
                                $fieldDef['class'] = array();
                                $fieldDef['shortClass'] = $shortClassName;
                                foreach ($this->model as $key => $value) {
                                    $fieldDef['class'][$key] = $value . '_' . $relation['class'];
                                }
                                $fieldDef['source'] = $classes[$relation['class']]['source'];
                                break;
                            case 'collection':
                            case 'cross':
                                break;
                            default:
                                $fieldDef['data'] = $fieldName;
                        }
                    }
                    if(!isset($fieldDef['readonly'])){
                        $fieldDef['readonly'] = false;
                    }
                    $fieldDef['readonly'] |= $ro;
                }
                

                if(isset($classDef['relations'])) {
                    $cd = $classDef['relations'];
                    foreach ($cd as $relationName => $relationDef) {
                        $class = &$classes[$relationDef['class']];
                        if(!isset($class['relations'])){
                            $class['relations'] = array();
                        } 
                        $inverse = $relationDef['inverse'];
                        if(!isset($class['relations'][$inverse['name']])) {
                            $class['relations'][$inverse['name']] = array(
                                'class' => $shortClassName,
                                'type' => $inverse['type'],
                                'inverse' => array('name'=>$relationName, 'type'=>$relationDef['type']),
                                'join' => $this->invert($relationDef['join'])
                            );
                        }
                    }
                }
            }
            foreach($classes as $shortClassName => &$classDef) {
                foreach($classDef['fields'] as $fieldName => &$fieldDef) {
                    switch($fieldDef['type']){
                        case 'collection':
                        case 'cross':
                            $fieldDef['readonly'] = true;
                            $relation = $classDef['relations'][$fieldName];
                            $fieldDef['data'] = $relation['join'];
                            $fieldDef['class'] = array();
                            $fieldDef['shortClass'] = $shortClassName;                            
                            foreach ($this->model as $key => $value) {
                                $fieldDef['class'][$key] = $value.'_'.self::pluralize($relation['class']);
                            }
                            $fieldDef['source'] = $classes[$relation['class']]['source'];
                            break;
                    }
                }
            }
            foreach($classes as $shortClassName => &$classDef) {
                foreach($classDef['fields'] as $fieldName => &$fieldDef) {
                    if(is_string($fieldDef['data'])){
                        $m = explode('/',$fieldDef['data']);
                        if(count($m)>1 && $fieldDef['type']==='object'){
                            $s = $classDef;
                            foreach ($m as $f) {
                                $s = $classes[$s['fields'][$f]['shortClass']];
                            }
                            if(!isset($fielDef['class'])) $fieldDef['class'] = array();
                            foreach ($this->model as $key => $value) {
                                $fieldDef['class'][$key] = $value . '_' . $s['shortClassName'];
                            }
                        }
                    }
                }
            }

            $x['{class:url}']->Oxygen_Common_ModelGenerator_Model($classes);
        }
    }

?>