<?
namespace oxygen\common\auth;
	use oxygen\controller\Controller;

	class Auth extends Controller {

		protected $session = null;
		const LOGON_SESSION_PREFIX = 'Logon::';
		private static $defaults = array(
			'message' => null,//'You are not signed in',
			'login' => false,
			'role' => false,
			'roles' => array(),
			'user_id' => 0,
			'user' => null,
			'last_activity' => 0
		);

		public function getIcon() {
			return 'key';

		}

		public function __toString() {
			return 'Authentification';
		}

		public function getAuthDb() {
		}

		public function __get($name){
			return $this->session->get(
				self::LOGON_SESSION_PREFIX . $name,
				self::$defaults[$name]
			);
		}

		public function __complete() {
			$this->session = $this->scope->SESSION;
		}

		public function __set($name, $value) {
			$this->session->put(
				self::LOGON_SESSION_PREFIX . $name,
				$value
			);
		}

		public function signOut($message = null) {
			foreach (self::$defaults as $key => $value) {
				if($key == 'message'){
					$this->{$key} = $message;
				}else{
					$this->{$key} = $value;
				}
			}
			return '';
		}

		public function getRolesFor($login, $password) {
			switch($login){
				case 'admin': return $password === '#admin#' ? array('admin','user') : array();
				case 'user': return $password === '#user#' ? array('user') : array();
				default:
				 return array();
			}
		}

		public function getUserId()
		{
			switch($this->login){
				case 'admin': return 1; 
				case 'user': return 2;
				default: return 0;
			}
		}

		public function authenticate($data) {
			$login = $this->login = $data['login'];
			$password = $data['password'];
			$roles = $this->roles = $this->getRolesFor($login, $password);
			$user_id = $this->user_id = $this->getUserId();
			if(count($roles)>0) {
				$role = $this->role = $roles[0];
				$this->message = $this->_("auth_as"). ' ' . $role;
			} else {
				$this->message = $this->_("try_again");
			}
			return '';
		}

        public function isLogged(){
            return count($this->roles)>0;
        }

		public function process($data) {
			$session = $this->scope->SESSION;
			if($this->role !== false) {
				if(isset($data['sign-out'])) return $this->signOut();
				if(isset($data['change-role'])) return $this->switchRoleTo($data[$role]);
            } 
			
			if(isset($data['authenticate'])) {
				$this->signOut();
				return $this->authenticate($data);
			}
			
			return '';
		}

		public function hasRole($role = array()){
			if(is_array($role)){
				foreach($role as $r){
					if(in_array($r, $this->roles)){
						return true;
					}
				}
				return false;
			}else{
				return in_array($role, $this->roles);
			}
		}
	}

?>