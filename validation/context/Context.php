<?
namespace oxygen\validation\context;

    use oxygen\object\Object;

    class Context extends Object {
        private $items = array();

        public function Error($owner,$message,$data = null) {
            $this->Add($this->scope->{'oxygen\\validation\\error\\Error'}($owner, $message, $data));
        }
        public function Warning($owner, $message, $data = null) {
            $this->Add($this->scope->{'oxygen\\validation\\warning\\Warning'}($owner, $message, $data));
        }
        public function Notice($owner, $message, $data = null) {
            $this->Add($this->scope->{'oxygen\\validation\\notice\\Notice'}($owner, $message, $data));
        }
        public function Add($item) {
            $severity = $item->getSeverity();
            if(!isset($this->items[$severity])) {
                $this->items[$severity] = array($item);
            } else {
                $this->items[$severity][] = $item;
            }
        }
    }


?>