<?
namespace oxygen\factory\callback;
use oxygen\factory\Factory;

class Callback extends Factory {
        public function getInstance($args = array(), $scope = null) {
            if ($scope === null) $scope = $this->scope;
            $callable = $this->getDefinition();
            array_unshift($args, $scope);
            return call_user_func_array($callable, $args);
        }
    }



?>