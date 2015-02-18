<?
namespace oxygen\list_object;

    use oxygen\object\Object;
    use oxygen\scope\Scope;

    class ListObject extends Object {
        public static function __class_construct() {
            $scope = Scope::root();
            $scope->register('List','ListObject');
        }
    }


?>