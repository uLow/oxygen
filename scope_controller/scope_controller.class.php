<?
namespace oxygen\scope_controller;
    use oxygen\controller\Oxygen_Controller;

    class Oxygen_ScopeController extends Oxygen_Controller {
        public function __depend($scope) {
            $this->scope = $scope->Scope();
        }
    }


?>