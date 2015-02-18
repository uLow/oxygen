<?
namespace oxygen\common\module\schema;

    use Exception;
    use oxygen\object\Object;

    class Schema extends Object {

        public $yml, $owner;
        public $uses = array();
        public $root;
        public $classes = array();
        public $databases = array();
        public $moduleClassName = '';

        public function __construct($yml, $owner, $moduleClassName) {
            $this->yml = $yml;
            $this->owner = $owner;
            $this->root = $yml['root'];
            $this->databases = $yml['databases'];
            $this->moduleClassName = $moduleClassName;
        }

        public function resolveUsings() {
            if(isset($this->yml['uses'])) {
                foreach ($this->yml['uses'] as $alias => $use) {
                    $class = str_replace('/', '_', $this->root . $use);
                    $this->uses[$alias] = $this->owner->loadSchemaFor($class);
                }
            }
        }

        public function getClass($shortName) {
            $parts = explode('.', $shortName, 2);
            if(count($parts) > 1) {
                $schemaAlias = $parts[0];
                $shortName = $parts[1];
                if(!isset($this->uses[$schemaAlias])) throw new Exception("unrecognized schema alias $schemaAlias");
                return $this->uses[$schemaAlias]->getClass($shortName);
            } else {
                return $this->classes[$shortName];
            }
        }

        public function getSource($shortName) {
            $parts = explode('.', $shortName, 2);
            if(count($parts) > 1) {
                $dbAlias = $parts[0];
                $shortName = $parts[1];
            } else {
                $dbAlias = 'default';
            }
            if(!isset($this->databases[$dbAlias])) throw new Exception("unrecognized db alias $dbAlias");
            return $this->databases[$dbAlias].'/'.$shortName;
        }

        public function initializeModels() {
            if(isset($this->yml['classes'])) {
                foreach ($this->yml['classes'] as $className => $classDef) {
                    $this->classes[$className] = $this->scope->Oxygen_Common_Module_ClassHandler(
                        $className,
                        $classDef, 
                        $this
                    );
                }
            }
        }

        public function resolveModelDependencies() {
            foreach ($this->classes as $class) {
                $class->resolveClassDependencies();
            }
        }

        public function generateClasses() {
            foreach ($this->classes as $class) {
                $class->generate();
            }
        }
    }


?>