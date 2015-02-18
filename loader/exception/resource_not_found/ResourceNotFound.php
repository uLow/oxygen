<?
namespace oxygen\loader\exception\resource_not_found;

    use oxygen\loader\exception\LoaderException;

class ResourceNotFound extends LoaderException {
        public function __construct($class,$resource) {
            parent::__construct("Resource '$resource' for class '$class' is not found");
        }
    }
?>
