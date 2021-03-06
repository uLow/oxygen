<?
namespace oxygen\factory\factory_handler;

use oxygen\factory\Factory;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;


class FactoryHandler extends Factory {

        public $reflector = null;
        public $construct = false;
        public $depend    = false;
        public $complete  = false;
        public $oxygen    = false;
        public $reflected = false;

        const DEPENDENCY_METHOD = '__depend';
        const COMPLETION_METHOD = '__complete';

        const ARGUMENTS_ARE_NOT_ACCEPTABLE = 'Arguments are not acceptable';

        private static function hasPublicConstructor($class) {
            try {
                $m = new ReflectionMethod($class, $class);
             if ($m->isPublic()) {
                 return true;
             }
            }
            catch (ReflectionException $e) {
            }
            try {
             $m = new ReflectionMethod($class,'__construct');
             if ($m->isPublic()) {
                 return true;
             }
            }
            catch (ReflectionException $e) {
            }
            return false;
        }

        private function ensureReflected() {
            if ($this->reflected) return;
            $class           = $this->getDefinition();
            $ref             = $this->reflector = new ReflectionClass($class);
            $this->construct = self::hasPublicConstructor($class);
            if (self::isOxygenClass($class)) {
                $this->oxygen   = true;
                $this->depend   = $ref->hasMethod(self::DEPENDENCY_METHOD);
                $this->complete = $ref->hasMethod(self::COMPLETION_METHOD);
            }
            $this->reflected = true;
        }

        public function getInstance($args = array(), $scope = null) {
            if (!$this->reflected) $this->ensureReflected();
            if($this->construct) {
                $instance = $this->reflector->newInstanceArgs($args);
            } else {
                $this->__assert(
                    count($args) == 0,
                    self::ARGUMENTS_ARE_NOT_ACCEPTABLE
                );
                $instance = $this->reflector->newInstance();
            }
            if($this->oxygen) {
                if($scope === null) $scope = $this->scope;
                if($this->depend) $instance->{self::DEPENDENCY_METHOD}($scope);
                if($this->complete) $instance->{self::COMPLETION_METHOD}();
            }
            return $instance;
        }

    }


?>