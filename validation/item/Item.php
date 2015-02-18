<?
namespace oxygen\validation\item;

    use oxygen\object\Object;

    abstract class Item extends Object {

        public $owner   = null;
        public $data    = null;
        public $message = null;

        public abstract function getSeverity();

        public function __construct($owner,$message,$data=null) {
            $this->owner   = $owner;
            $this->message = $message;
            $this->data    = $data;
        }
    }

?>