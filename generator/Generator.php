<?
namespace oxygen\generator;
    use ArrayObject;
    use oxygen\loader\Loader;
    use oxygen\object\Object;
    use oxygen\utils\yaml\YAML;

    class Generator extends Object {
        private $models = null;
        public function __complete() {
            $this->models = new ArrayObject();
            $this->scope->models = $this->models;
        }

        private function collectPaths($start, &$paths = array()) {
            foreach(glob($start . '*.yml') as $file) {
                $paths[] = $file;
            }
            foreach(glob($start . '*') as $dir){
                if(is_dir($dir)) {
                     $this->collectPaths($dir . DIRECTORY_SEPARATOR, $paths);
                }
            }
            return $paths;
        }

        public function generate() {
            $paths = $this->collectPaths(Loader::CLASS_PATH . DIRECTORY_SEPARATOR);
            $tpltime = filemtime(Loader::pathFor('oxygen\\meta\\Meta','model_base.php'));
            foreach($paths as $path) {
                try {
                    $class = Loader::classFor(dirname($path));
                    $yaml  = YAML::load($path);
                    $time  = filemtime($path);
                    $meta  = $this->scope->{'oxygen\\meta\\Meta'}($class,$yaml,max($time,$tpltime));
                } catch (\oxygen\exception\Exception $e) {
                    $this->throwException('YAML Error',0,$e);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            foreach($this->models as $model) {
                $model_path = Loader::pathFor(
                    $model->getModelName(),false,false
                );
                $model_base_path = Loader::pathFor(
                    $model->getModelBaseName(),false,false
                );
                if(!file_exists($model_base_path)
                  || filemtime($model_base_path) < $model->time
                ) {
                    $f = fopen($model_base_path,'w');
                    fwrite($f,$model->get->model_base());
                    echo "Generated $model_base_path\n";
                    fclose($f);
                }

                if(!file_exists($model_path)) {
                    $f = fopen($model_path,'w');
                    fwrite($f,$model->get->model());
                    echo "Generated $model_path\n";
                    fclose($f);
                }
            }
        }
    }


?>