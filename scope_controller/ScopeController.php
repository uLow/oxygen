<?
namespace oxygen\scope_controller;
    use oxygen\controller\Controller;

    class ScopeController extends Controller {
        public function __depend($scope) {
            $this->scope = $scope->Scope();
        }
    }


?>