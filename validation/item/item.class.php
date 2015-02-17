<?
namespace oxygen\validation\item;

    use oxygen\object\Oxygen_Object;

    abstract class Oxygen_Validation_Item extends Oxygen_Object {

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