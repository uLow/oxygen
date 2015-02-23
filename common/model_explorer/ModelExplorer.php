<?
namespace oxygen\common\model_explorer;

    use ArrayAccess;
    use Countable;
    use Iterator;
    use IteratorAggregate;
    use oxygen\controller\Controller;
    use oxygen\entity\Entity;

    class ModelExplorer extends Controller {

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
            return $this->model instanceof Entity;
        }

        public function configure($x) {
            if ($this->isCollection($this->model)) {
                $x[$this->getPattern()]->{'oxygen\\common\\model_explorer\\ModelExplorer'}($this->model);
            } else if ($this->isEntity()) {
                $fields = call_user_func(array(get_class($this->model),'__getFields'));
                foreach ($fields as $f => $field) {
                    $x[$f]->{'oxygen\\common\\model_explorer\\ModelExplorer'}($f[$this->model]);
                }
            }
        }
    }

?>