<?
namespace oxygen\common\module\schema;

    use oxygen\dumper\Dumper;
    use oxygen\object\Object;
    use oxygen\utils\text\Text;

    class Schema extends Object {

        public $yml, $owner;
        public $uses = array();
        public $classes = array();
        public $databases = array();
        public $moduleClassName = '';

        public function __construct($yml, $owner, $moduleClassName) {
            $this->yml = $yml;
            $this->owner = $owner;
            $this->namespace = $yml['namespace'];
            $this->databases = $yml['databases'];
            $this->moduleClassName = $moduleClassName;
            $ns = explode('\\', $moduleClassName);
            unset($ns[count($ns)-1]);
            $this->originNamespace = implode('\\', $ns);
        }

        public function resolveUsings() {
            if(isset($this->yml['uses'])) {
                foreach ($this->yml['uses'] as $alias => $use) {
                    $this->uses[$alias] = $this->owner->loadSchemaFor($use);
                }
            }
        }

        public function getClass($shortName) {
            $parts = explode('.', $shortName, 2);
            if(count($parts) > 1) {
                $schemaAlias = $parts[0];
                $shortName = $parts[1];
                if(!isset($this->uses[$schemaAlias])) throw new \Exception("unrecognized schema alias $schemaAlias");
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
            if(!isset($this->databases[$dbAlias])) throw new \Exception("unrecognized db alias $dbAlias");
            return $this->databases[$dbAlias].'/'.$shortName;
        }

        public function initializeModels() {
            if(isset($this->yml['classes'])) {
                foreach ($this->yml['classes'] as $className => $classDef) {
                    $this->classes[$className] = $this->scope->{'oxygen\\common\\module\\class_handler\\ClassHandler'}(
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