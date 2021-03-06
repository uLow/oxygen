<?
namespace oxygen\serializer;

	use oxygen\object\Object;

	class Serializer extends Object {
		private $array = array();

		public function add(&$item) {
			$count = count($this->array);
			$this->array[$count] = &$item;
		}
		public function put_data() {
			echo serialize($this->data);
		}

		public function put_if_needed() {
			if(count($this->array)) $this->put_view();
		}
	}

?>