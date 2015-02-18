<?
namespace oxygen\validation\item\warning;

    use oxygen\validation\item\Item;
    use oxygen\validation\Validation;

    class Warning extends Item {
        public function getSeverity() {
            return Validation::WARNING;
        }
    }

?>