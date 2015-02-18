<?
namespace oxygen\field\cross;
    use oxygen\field\Field;

    class Cross extends Field {
        public function wrap($cond) {
            $data = call_user_func(array($this->yaml['entity-class'],'all'));
            return $data->where($cond);
        }
    }

?>