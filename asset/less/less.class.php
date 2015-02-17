<?
namespace oxygen\asset\less;
use Exception;
use oxygen\asset\Oxygen_Asset;
use oxygen\utils\text\Oxygen_Utils_Text;

class Oxygen_Asset_LESS extends Oxygen_Asset {

		public static function __class_construct($scope) {
			$scope->lib->load('lessphp/lessc.inc.php');
		}

		private $less = null;

		const VIRTUAL_WRAPPER = '.{0}{{1}}';
		const CSS_URL = 'url({0})';

		public function __construct() {
			parent::__construct('.less');
			$this->less = new lessc();
			$this->less->registerFunction('icon', array($this, 'icon'));
			$this->less->registerFunction('virtual', array($this, 'resource'));
		}

		public function icon($parsed) {
			list($type, $value) = $parsed;
			
			$point = strrpos($value, '.');
			if($point === false){
				$ext = 'png';
			}else{
				$parts = array('value' => substr($value, 1, $point-1), 'ext' => substr($value, $point+1, -1));
				$value = $parts['value'];
				$ext = $parts['ext'];
			}

			
			$this->__assert(($type === 'keyword' or $type === 'string'), 'Invalid icon code');
			$this->__assert(preg_match("/^[a-z_-]+$/", $value), 'Invalid icon code');
			return Oxygen_Utils_Text::format(
                self::CSS_URL,
                $this->scope->assets->getIcon($value, $ext)
            );
		}

		public function resource($parsed) {
			$resource = trim($parsed[2][0][1],'\'');
			$class = trim($parsed[2][1][1],'\'');
			return 'url('.$this->scope->loader->urlFor($class, $resource).')';
		}

		public function processOne($asset) {
			$source = parent::processOne($asset);
			$source = preg_replace("/resource\((.*)\)/", "virtual(\\1,'{$asset->class}')", $source);
			if($asset->component !== false){
				return Oxygen_Utils_Text::format(
					self::VIRTUAL_WRAPPER,
					$asset->component,
					$source
				);
			} else {
				return $source;
			}
		}

		protected function process($source) {
			try {
				return $this->less->parse($source);
			} catch(Exception $ex) {
				$this->flash($ex->getMessage(),'error');
				return '/* ERROR ('.$ex->getMessage().') */';
			}
		}
	}


?>