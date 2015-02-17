<?
namespace oxygen\field\collection;
    use oxygen\field\Oxygen_Field;

    class Oxygen_Field_Collection extends Oxygen_Field {
        public function wrap($cond) {
            $data = call_user_func(array($this->yaml['entity-class'],'all'));
            return $data->where($cond);
        }
    }

?>