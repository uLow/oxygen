<?php
namespace oxygen\factory\callback;

use oxygen\factory\Factory;
use oxygen\scope\Scope;

class Callback extends Factory
{
    /**
     * @param array $args
     * @param Scope $scope
     * @return mixed
     */
    public function getInstance($args = array(), $scope = null)
    {
        if ($scope === null) /** @var Scope $scope */
            $scope = $this->scope;

        $callable = $this->getDefinition();
        array_unshift($args, $scope);
        return call_user_func_array($callable, $args);
    }
}


?>