<?
namespace oxygen\field;
    use ArrayAccess;
    use oxygen\object\Object;

    abstract class Field extends Object implements ArrayAccess {

        const DATA = 'data';
        const I18N = 'i18n';

        public $yaml  = null;
        public $owner = '';
        public $name  = '';
        public $data  = '';
        public $i18n  = false;

        public static $field_types = array(
            'integer',
            'string',
            'unixtime',
            'minor',
            'object',
            'password',
            'set',
            'ip',
            'json',
            'text',
            'double',
            'collection',
            'cross',
        );

        public static function getFieldTypes(){
            return self::$field_types;
        }

        public function option($name,$default = null) {
            if(isset($this->yaml[$name])) {
                return $this->yaml[$name];
            } else {
                return $default;
            }
        }

        public function isReadOnly() {
            return isset($this->yaml['readonly']) && $this->yaml['readonly']===true;
        }

        public function label() {
            return isset($this->yaml['label'])?$this->yaml['label']:$this->name;
        }

        public function nameFor($prefix) {
            return preg_replace_callback(
                '/_([a-z])/i',
                function($m){
                    return ucfirst($m[1]);
                },
                $prefix.'_'.$this->name
            );
        }

        public function __construct($owner,$name, $yaml) {
            $this->yaml  = $yaml;
            $this->owner = $owner;
            $this->name  = $name;
            $this->data  = $this->option(self::DATA, $name);
            $this->i18n  = $this->option(self::I18N, false);
        }


        protected function wrapAll($value) {
            if($this->i18n) {
                $result = array();
                foreach($value as $lang => $v) {
                    $result[$lang] = $this->wrap($v);
                }
            } else {
                $result = $this->wrap($value);
            }
            return $result;
        }

        protected function unwrapAll($value) {
            if($this->i18n) {
                $result = array();
                foreach($value as $lang => $v) {
                    $result[$lang] = $this->unwrap($v);
                }
            } else {
                $result = $this->unwrap($value);
            }
            return $result;
        }

        protected function wrap($value) {
            return $value;
        }

        protected function unwrap($value) {
            return $value;
        }

        public function offsetSet($instance, $value) {
            return $instance[$this->data] = $this->unwrapAll($value);
        }

        public function offsetGet($instance) {
            return $this->wrapAll($instance[$this->data]);
        }

        public function offsetUnset($instance) {
            unset($instance[$this->data]);
        }

        public function offsetExists($instance) {
            return isset($instance[$this->data]);
        }

    }


?>