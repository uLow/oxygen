<?
namespace oxygen\exception\wrapper;
    use oxygen\exception\Exception;

    class Wrapper extends Exception {
        private $ex = null;
        public function __construct($ex) {
            parent::__construct($ex->getMessage());
            $this->ex = $ex;
        }
        public function __toString() {
            return (string)$this->ex;
        }

        public function getException(){
            return $this->ex;
        }
        
        public function getWrapTrace() {
            return $this->ex->getTrace();
        }
        
        public function getName() {
            return get_class($this->ex);
        }        
        
    }

?>