<?
    class Oxygen_ItemPicker extends Oxygen_Object {
        
        private $method;
        public $buttons;
        public $owner;
        public $name;
        public $extraParams = array();

        public function __construct($owner, $name, $method, $buttons = array(), $extraParams = array()) {
            $this->method = $method;
            $this->buttons = $buttons;
            $this->owner = $owner;
            $this->name = $name;
            $this->extraParams = $extraParams;
        }

        public function go() {
            return $this->owner->go();
        }

        public function rpc_getData($criteria) {
            $params = array($criteria);
            foreach($this->extraParams as $param){
                $params[] = $param;
            }
            return $this->embed_results(call_user_func_array($this->method, $params));
        }

    }