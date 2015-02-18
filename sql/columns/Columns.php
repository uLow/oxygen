<?
namespace oxygen\sql\columns;

	use oxygen\controller\Controller;

class Columns extends Controller {
		public function configure($x) {
			$x['{COLUMN_NAME:url}']->Column($this->model);
		}

        public function getDefaultView() {
            return 'as_table';
        }
	}

?>	