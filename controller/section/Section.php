<?
namespace oxygen\controller\section;
	use ArrayAccess;
	use Countable;
	use IteratorAggregate;
	use oxygen\object\Object;

	class Section
		extends Object
		implements ArrayAccess, IteratorAggregate, Countable
	{

		private $controller = null;
		private $router = null;

		public function __construct($controller, $router){
			$this->controller = $controller;
			$this->router = $router;
		}

		public function offsetGet($offset) {
			return $this->router[$offset];
		}

		public function offsetSet($offset, $value) {
			$this->router[$offset] = $value;
		}

		public function offsetUnset($offset) {
			unset($this->router[$offset]);
		}

		public function offsetExists($offset) {
			return isset($this->router[$offset]);
		}

		public function count() {
			return count($this->router);
		}

		public function getIterator() {
			return $this->scope->ChildrenIterator($this->controller,array($this->router));
		}
	}


?>