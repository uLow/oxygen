<?
namespace oxygen\sql\database;

	use oxygen\scope_controller\ScopeController;

    class Database extends ScopeController {

        public $connection = null;

        public function __complete() {
            $this->connection = $this->scope->connection;
            $this->scope->database = $this;
        }

        public function getIcon() {
            return 'database';
        }

        public function getDefaultView() {
            return 'as_table';
        }

		public function configure($x){
            $x['{table:url}']->Table($this->model['tables']);
		}
	}

?>