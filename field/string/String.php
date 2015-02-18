<?
namespace oxygen\field\string;

    use oxygen\field\Field;

    class String extends Field {
        public function offsetGet($instance) {
            return $this->wrapAll($instance[$this->data]);
        }
    }


?>