<?
namespace oxygen\common\model_generator\model;

    use oxygen\controller\Controller;

    class Model extends Controller {
        public function getIcon() {
            return 'plugin';
        }
        public function resolveFieldClass($type) {
            $t = preg_replace("/(^|_)([a-z])/e", "ucfirst('\\2')", $type);
            return 'oxygen\\field\\'.strtolower($t).'\\'.$t;
        }

        public function __toString() {
            return preg_replace('/([a-z])([A-Z])/','\\1 \\2',str_replace('_',' ',$this->model['shortClassName']));
        }

        public static function getWritableDir($base, $dir = '.') {
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

        public function generate() {
            foreach ($this->model['classBase'] as $type => $base) {
                $this->getWritableDir(dirname($base));
                file_put_contents($base, $this->{'get_'.$type.'_base'}());
                $file = $this->model['classFile'][$type];
                if (!file_exists($file)) {
                    $this->getWritableDir(dirname($file));
                    file_put_contents($file, $this->{'get_'.$type.'_class'}());
                }
            }
            foreach ($this->model['classBaseCollection'] as $type => $base) {
                $this->getWritableDir(dirname($base));
                file_put_contents($base, $this->{'get_'.$type.'_base_collection'}());
                $file = $this->model['classFileCollection'][$type];
                if (!file_exists($file)) {
                    $this->getWritableDir(dirname($file));
                    file_put_contents($file, $this->{'get_'.$type.'_class_collection'}());
                }
            }
        }

        public function configure($x) {
            foreach ($this->model['fields'] as $fieldName => $fieldDef) {
                $fieldClass = $this->resolveFieldClass($fieldDef['type']);
                $f = $this->scope->$fieldClass($this->model['className'], $fieldName, $fieldDef);
                $x[$fieldName]->Oxygen_Common_ModelGenerator_Field($f);
            }
        }
    }

?>