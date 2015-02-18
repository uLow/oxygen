<?
namespace oxygen\factory;
use oxygen\object\Object;

abstract class Factory extends Object {
        private $definition = null;
        public final function __construct($definition) {
            $this->definition = $definition;
        }
        public abstract function getInstance($args = array(), $scope = null);
        public final function getDefinition(){
            return $this->definition;
        }
        public function __wakeup() {
           $this->reflected = false;
        }
    }


?>