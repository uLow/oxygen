<?
namespace oxygen\lib;

    use oxygen\object\Object;

    class Lib extends Object {

        public function load($path) {
            require_once $this->path($path);
        }

        public function path($path) {
            return  $this->scope->LIB_PATH . $path;
        }

        public function url($path) {
            //$oxygeRealPath = realpath("oxygen/..");
            $oxygeRealPath = CURRENT_ROOT_PATH;
            //die("LIB: ".$this->path($path). " :: ". $oxygeRealPath);
        	$url = str_replace($oxygeRealPath, '', $this->path($path));
        	$url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        	return $url;
        }
        
        public static function __class_construct($scope) {
            $scope->LIB_PATH = CURRENT_ROOT_PATH . DIRECTORY_SEPARATOR . "oxygen" . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR;
        }
    }


?>