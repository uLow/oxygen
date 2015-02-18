<?
namespace oxygen\validation\item\error;

    use oxygen\validation\item\Item;
    use oxygen\validation\Validation;

    class Error extends Item {
        public function getSeverity() {
            return Validation::ERROR;
        }
    }

?>