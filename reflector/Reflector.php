<?
namespace oxygen\reflector;

    use oxygen\object\Object;

    class Reflector extends Object {
        public $data = null;
        public function __construct($data){
            $this->data = $data;
        }
        public function __complete() {
        }
        
    }

?>