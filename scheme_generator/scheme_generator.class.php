<?
    class Oxygen_SchemeGenerator extends Oxygen_Controller{
        public $yml = null;
        public function __toString(){
            return 'Scheme generator';
        }

        public function __complete(){
            $this->scope->lib->load('yaml-php/lib/sfYaml.php');
            $file = $this->scope->loader->pathFor($this->model,'schema.yml');
            $this->yml = sfYaml::load($file);
            $this->field_types = Oxygen_Field::getFieldTypes();
        }

        public function rpc_saveSchema($args){
            $schema = json_decode(json_encode($args->schema), true);
            ksort($schema['classes']);
            $yml = sfYaml::dump($schema, 4);

            $file = $this->scope->loader->pathFor($this->model,'schema.yml');
            file_put_contents($file, $yml);
            return $this->scope->application->generateClasses();
        }
    }