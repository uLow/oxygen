<?
namespace oxygen\field\object;
    use oxygen\field\Oxygen_Field;

    class Oxygen_Field_Object extends Oxygen_Field {
        public function wrap($cond) {
            $data = call_user_func(array($this->yaml['entity-class'],'all'));
            return $data[$cond];
        }

        public function unwrap($object)
        {
        	return $object[$this->yaml['key']];
        }
    }


?>