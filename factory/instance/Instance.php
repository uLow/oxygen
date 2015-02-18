<?
namespace oxygen\factory\instance;

use oxygen\factory\Factory;

class Instance extends Factory {
        public function getInstance($args = array(), $scope = null) {
            return $this->getDefinition();
        }
    }


?>