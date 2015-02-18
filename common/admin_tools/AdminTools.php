<?
namespace oxygen\common\admin_tools;
    use oxygen\controller\Controller;

    class AdminTools extends Controller {

        public function configure($x){
            $x['dbs'] = $this->scope->connection;
            $x['auth'] = $this->scope->__authenticated();
        }

        public function __toString() {
            return 'Admin tools';
        }

        public function getIcon() {
            return 'wrench';
        }
    }
?>