<?
namespace oxygen\common\file_upload\format;
    use oxygen\controller\Controller;

class Format extends Controller {
        protected $args = null;
        public $id;
        public $title;
        
        public function __construct($args, $title = false, $id = false) {
            $this->$args = $args;
            $this->id = $id;
            $this->title = $title;
        }
        public function __toString() {
            return $this->title;
        }
    }

?>    