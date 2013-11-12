<?
    class Oxygen_Field_Minor extends Oxygen_Field {
        public function wrap($value){
            return $value/100;
        }

        public function unwrap($value){
            return $value*100;
        }
    }