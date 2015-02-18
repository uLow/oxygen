<?
namespace oxygen\validation\item\notice;

    use oxygen\validation\item\Item;
    use oxygen\validation\Validation;

    class Notice extends Item {
        public function getSeverity() {
            return Validation::NOTICE;
        }
    }


?>