<?
namespace oxygen\sql\keys;

    use oxygen\controller\Oxygen_Controller;

    class Oxygen_SQL_Keys extends Oxygen_Controller {
        public function configure($x) {
            $x['{name:url}']->Key($this->model);
        }
    }
?>