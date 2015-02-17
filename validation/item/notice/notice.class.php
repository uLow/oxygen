<?
namespace oxygen\validation\item\notice;

    use oxygen\validation\item\Oxygen_Validation_Item;
    use oxygen\validation\Oxygen_Validation;

    class Oxygen_Validation_Item_Notice extends Oxygen_Validation_Item {
        public function getSeverity() {
            return Oxygen_Validation::NOTICE;
        }
    }


?>