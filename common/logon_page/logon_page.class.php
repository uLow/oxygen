<?

	class Oxygen_Common_LogonPage extends Oxygen_Controller {
		public function configure($x) {
			$x['{url:any}']->LogonPage();
		}
		public function getIcon() {
			return 'key';
		}
		public function __toString() {
			if($this->scope->auth->role) {
				return $this->_("roles");
			} else {
				return $this->_("log_in");
			}
		}
		public function handlePost() {
			return $this->scope->auth->process($this->scope->POST);
		}
	}

?>