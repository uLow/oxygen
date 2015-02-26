<?
namespace oxygen\scope_controller;
    use oxygen\controller\Controller;

    class ScopeController extends Controller {
        /**
         * @param \oxygen\scope\Scope $scope
         */
        public function __depend($scope) {
            $this->scope = $scope->Scope();
        }
    }


?>