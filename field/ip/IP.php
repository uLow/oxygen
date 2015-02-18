<?
namespace oxygen\field\ip;

    use oxygen\field\Field;

    class IP extends Field {
        public function wrap($value){
            return long2ip($value);
        }

        public function unwrap($value){
            return ip2long($value);
        }
    }

?>