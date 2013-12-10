<?

    class Oxygen_Field_IP extends Oxygen_Field {
        public function wrap($value){
            return long2ip($value);
        }

        public function unwrap($value){
            return ip2long($value);
        }
    }

?>