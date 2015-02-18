<?
namespace oxygen\controller\dummy;
	use oxygen\controller\Controller;

	class Dummy extends Controller {
		//private $icon = '';
		public function __construct($name = 'Just', $icon = 'bullet_black'){
			parent::__construct($name);
			$this->icon = $icon;
		}
		public function getIcon() {
			return $this->icon;
		}
		public function __toString() {
			return (string)$this->model;
		}
	}

?>