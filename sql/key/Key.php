<?
namespace oxygen\sql\key;

	use oxygen\sql\columns\Columns;

    class Key extends Columns {
        public function getDefaultView() {
            return 'view';
        }

        function rpc_Hello($whom) {
            $this->flash('Hi!');
            $this->flashError('Other!' . $whom);
            return "Hi, {$whom}!";
        }
        
        public function configure($x) {
            $x['{COLUMN_NAME:url}']->Column($this->model['columns']);
        }
	}

?>