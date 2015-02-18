<?
namespace oxygen\validation\rule\field;

    use oxygen\validation\rule\Rule;

    abstract class Field extends Rule {
        public $filed = null;
        public function __construct($filed) {
            $this->field = $filed;
        }
    }


?>