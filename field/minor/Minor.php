<?
namespace oxygen\field\minor;
    use oxygen\field\Field;

    class Minor extends Field {
        public function wrap($value){
            return $value/100;
        }

        public function unwrap($value){
            return $value*100;
        }
    }