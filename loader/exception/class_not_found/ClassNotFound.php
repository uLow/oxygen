<?
namespace oxygen\loader\exception\class_not_found;

    use oxygen\loader\exception\LoaderException;

class ClassNotFound extends LoaderException {
        public function __construct($class) {
            parent::__construct("Class '$class' is not found");
        }
    }

?>

