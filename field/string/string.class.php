<?

    class Oxygen_Field_String extends Oxygen_Field {
        public function offsetGet($instance) {
            return $this->wrapAll($instance[$this->data]);
        }
    }


?>