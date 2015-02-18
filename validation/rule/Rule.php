<?
namespace oxygen\validation\rule;

    use oxygen\object\Object;

    abstract class Rule extends Object {
        public abstract function validate($instance);
        protected function Error($owner,$message,$data = null) {
            $this->scope->validationContext->Error($owner, $message, $data);
        }
    }


?>