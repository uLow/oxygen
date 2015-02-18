<?
namespace oxygen\field\set;

    use oxygen\field\Field;

    class Set extends Field {
        public function wrap($value) {
			return explode(',', $value);
        }
        public function unwrap($array) {
			return implode(',', $array);
        }
    }


?>