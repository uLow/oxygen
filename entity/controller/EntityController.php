<?
namespace oxygen\entity\controller;
    use oxygen\controller\Controller;
    use tpro\ams\entity\action_log\ActionLog;

    class EntityController extends Controller {
        public function rpc_UpdateCell($args) {
            $this->model[$args->source]=$args->current;
            //TODO: change direct edit to specified controller
            if(preg_match("/trx_codes/", $args->source)){
            	ActionLog::logAction("<b>".$this->model["trx_code"]."</b>[".$args->source."]: &laquo;".$args->original."&raquo; &rarr; &laquo;".$args->current."&raquo;", 0, $this->scope->auth->getCurrentUser()->getManagerId(), 3);
            }
            return $this->model->__submit();
        }

    }

?>