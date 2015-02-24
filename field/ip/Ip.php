<?
namespace oxygen\field\ip;

    use oxygen\field\Field;

    class Ip extends Field {
        public function wrap($value){
            return long2ip($value);
        }

        public function unwrap($value){
            return ip2long($value);
        }
    }

?>