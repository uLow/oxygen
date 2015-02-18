<?
namespace oxygen\common\form;

    use oxygen\controller\Controller;

    class Form extends Controller {
        public function __toString() {
            return (string)$this->model;
        }
    }
    
?>