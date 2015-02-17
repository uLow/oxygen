<?
namespace oxygen\common\model_explorer;

    use ArrayAccess;
    use Countable;
    use Iterator;
    use IteratorAggregate;
    use oxygen\controller\Oxygen_Controller;
    use oxygen\entity\Oxygen_Entity;

    class Oxygen_Common_ModelExplorer extends Oxygen_Controller {

        private function isCollection() {
            $a = $this->model;
            if(is_array($a)) return true;
            return is_object($a)
                && $a instanceof ArrayAccess
                && ($a instanceof IteratorAggregate || $a instanceof Iterator)
                && $a instanceof Countable
            ;
        }

        private function isEntity() {
            return $this->model instanceof Oxygen_Entity;
        }

        public function configure($x) {
            if ($this->isCollection($this->model)) {
                $x[$this->getPattern()]->Oxygen_Common_ModelExplorer($this->model);
            } else if ($this->isEntity()) {
                $fields = call_user_func(array(get_class($this->model),'__getFields'));
                foreach ($fields as $f => $field) {
                    $x[$f]->Oxygen_Common_ModelExplorer($f[$this->model]);
                }
            }
        }
    }

?>