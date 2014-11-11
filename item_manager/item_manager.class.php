<?
    class Oxygen_ItemManager extends Oxygen_Object {

        public $owner = null;
        public $picker = null;
        public $data = null;
        public $pick_method = null;
        public $buttons = null;
        public $extraParams = array();

        public function __construct($owner,$name,$data,$pick_method,$buttons,$extraParams = array()) {
            $this->owner = $owner;
            $this->data = $data;
            $this->pick_method = $pick_method;
            $this->buttons = $buttons;
            $this->name = $name;
            $this->extraParams = $extraParams;
        }

        public function __complete() {
            $picker_name = $this->name . '/picker';
            $this->picker = $this->scope->Oxygen_ItemPicker(
                $this->owner,
                $picker_name,
                $this->pick_method,
                $this->buttons,
                $this->extraParams
            );
            $this->owner->registerComponent($picker_name,$this->picker);
        }

        public function go() {
            return $this->owner->go();
        }

        private function getEntity($args) {
            //TODO: SecurityCheck
            $call = array($args->entityClass,'__getByKey');
            return call_user_func($call,json_decode($args->key));
        }

        private function getMethod($args, $name) {
            if(!isset($this->data[$args->section])){
                throw new Exception("No data section named {$args->section}");
            } 
            $section = $this->data[$args->section];
            if(!isset($section[1][$name])) {
                throw new Exception("Method {$name} is unavailable for {$args->section}");
            }
            return $section[1][$name];
        }

        public function rpc_Add($args) {
            $entity = $this->getEntity($args);
            $method = $this->getMethod($args,'add');
            $message = call_user_func($method, $entity);
            if ($message) $this->flash($message);
        }

        public function rpc_Delete($args) {
            $entity = $this->getEntity($args);
            $method = $this->getMethod($args,'delete');
            $message = call_user_func($method, $entity);
            if ($message) $this->flash($message);
        }


    }