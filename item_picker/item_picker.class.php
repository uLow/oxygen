<?
    class Oxygen_ItemPicker extends Oxygen_Object {
        
        private $method;
        public $buttons;
        public $owner;
        public $name;

        public function __construct($owner, $name, $method, $buttons = array()) {
            $this->method = $method;
            $this->buttons = $buttons;
            $this->owner = $owner;
            $this->name = $name;
        }

        public function go() {
            return $this->owner->go();
        }

        public function rpc_getData($criteria) {
            return $this->embed_results(call_user_func($this->method, $criteria));
        }

    }