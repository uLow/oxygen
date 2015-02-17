<?
namespace oxygen\list_object;

    use oxygen\object\Oxygen_Object;
    use oxygen\scope\Oxygen_Scope;

    class Oxygen_ListObject extends Oxygen_Object {
        public static function __class_construct() {
            $scope = Oxygen_Scope::root();
            $scope->register('List','Oxygen_ListObject');
        }
    }


?>