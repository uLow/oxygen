<?
namespace oxygen\common\form;

    use oxygen\controller\Oxygen_Controller;

    class Oxygen_Common_Form extends Oxygen_Controller {
        public function __toString() {
            return (string)$this->model;
        }
    }
    
?>