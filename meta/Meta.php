<?
namespace oxygen\meta;

    use oxygen\loader\Loader;
    use oxygen\object\Object;
    use oxygen\utils\text\Text;

    class Meta extends Object {

        const FIELDS = 'fields';
        const FIELD_TYPE = 'type';
        const DEFAULT_MODEL_PARENT = 'oxygen\\model\\Model';

        //const TYPE_CLASS = 'Oxygen_Field_{0}';
        const TYPE_IS_MISSING = 'There is not type for field {0}->{1}';
        const FIELDS_PART_MISSING = 'Fileds part missing for class {0} in schema file';

        public  $def    = null;
        private $fields = array();
        private $class  = '';
        public  $time   = 0;

        public function __construct($class, $def, $time) {
            $this->def   = $def;
            $this->class = $class;
            $this->time  = $time;
            if(!isset($def[self::FIELDS])) {
                $this->throwException(
                    Text::format(self::FIELDS_PART_MISSING, $class)
                );
            }
            foreach($def[self::FIELDS] as $name => $field_def) {
                if(!isset($field_def[self::FIELD_TYPE])) $this->throwException(
                    Text::format(self::TYPE_IS_MISSING, $class, $name)
                );
                $class = Text::ns(Loader::correctCase(
                    Text::format(self::TYPE_CLASS, ucfirst($field_def[self::FIELD_TYPE]))
                ));
                $this->fields[$name] = $this->scope->$class($this,$name,$field_def);
            }
        }

        public function getModelName() {
            return $this->{'class'};
        }

        public function getModelBaseName() {
            return $this->{'class'} . Loader::BASE_SUFFIX;
        }

        public function getModelParent() {
            return self::DEFAULT_MODEL_PARENT;
        }

        public function getFields() {
            return $this->fields;
        }

        public function __complete() {
            $this->scope->models[$this->class] = $this;
        }

        public function resolveRelations() {

        }

    }



?>