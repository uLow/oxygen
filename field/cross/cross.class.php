<?
namespace oxygen\field\cross;
    use oxygen\field\Oxygen_Field;

    class Oxygen_Field_Cross extends Oxygen_Field {
        public function wrap($cond) {
            $data = call_user_func(array($this->yaml['entity-class'],'all'));
            return $data->where($cond);
        }
    }

?>