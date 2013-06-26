<?

    class Oxygen_Common_Module extends Oxygen_ScopeController {

        public $schemata = array();
        public $icon = 'plugin';
        public $name = '';

        public function __construct($name='', $icon='plugin')
        {
            if($icon!==$this->icon){
                $this->icon = $icon;
            }

            if($name!==''){
                $this->name = $name;
            }
        }

        public function __toString()
        {
            return $this->name;
        }

        public function getIcon() {
            return $this->icon;
        }

        public function generateClasses($class = false) {
            if($class === false){
                $class = get_class($this);
            }
            $mainSchema = $this->loadSchemaFor($class);
            foreach ($this->schemata as $s) {
                $s->initializeModels();
            }
            foreach ($this->schemata as $s) {
                $s->resolveModelDependencies();
            }
            foreach ($this->schemata as $s) {
                $s->generateClasses();
            }
            return implode(',', array_keys($this->schemata));
        }

        public function loadSchemaFor($className) {
            if(!isset($this->schemata[$className])) {
                $this->scope->lib->load('yaml-php/lib/sfYaml.php');
                try {
                    $file = $this->scope->loader->pathFor($className,'schema.yml');
                    $yml = sfYaml::load($file);
                    $s = $this->schemata[$className] = $this->scope->Oxygen_Common_Module_Schema(
                        $yml, 
                        $this, 
                        $className
                    );
                    $s->resolveUsings();
                } catch(Exception $e) {
                    $this->throw_Exception($e->getMessage() . ' in ' . $className);
                }
            }
            return $this->schemata[$className];
        }

        public function rpc_Generate() {
            return $this->generateClasses('TPRO_AMS');
        }

    }
?>