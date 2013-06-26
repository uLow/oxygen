<?

    class Oxygen_Field_Set extends Oxygen_Field {
        public function wrap($value) {
			return explode(',', $value);
        }
        public function unwrap($array) {
			return implode(',', $array);
        }
    }


?>