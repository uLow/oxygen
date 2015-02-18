<?
namespace oxygen\sql\keys;

    use oxygen\controller\Controller;

    class Keys extends Controller {
        public function configure($x) {
            $x['{name:url}']->Key($this->model);
        }
    }
?>