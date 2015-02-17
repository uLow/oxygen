<?
namespace oxygen\validation\item\error;

    use oxygen\validation\item\Oxygen_Validation_Item;
    use oxygen\validation\Oxygen_Validation;

    class Oxygen_Validation_Item_Error extends Oxygen_Validation_Item {
        public function getSeverity() {
            return Oxygen_Validation::ERROR;
        }
    }

?>