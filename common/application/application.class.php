<?

	class Oxygen_Common_Application extends Oxygen_Controller {

        public $company = 'YourCompany';
        public $auth;
        public $config;
        public $language = null;

        public function __complete() {
            $this->auth = $this->scope->__authenticated();
            $this->configureLanguages();
        }

        public function configureLanguages($languageClass='Oxygen_Language', $params=array())
        {
            $root = Oxygen_Scope::getRoot();
            $this->scope->register('Language', $languageClass);
            $this->language = $this->scope->Language();
            $this->language->setup($params);
            if(isset($_GET['lang']) && isset($this->language->languages[$_GET['lang']])){
                $this->scope->SESSION['lang'] = $_GET['lang'];
            }

            if(!isset($this->scope->SESSION['lang']) or !isset($this->language->languages[$this->scope->SESSION['lang']])){
                if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'], $this->language->languages[$this->parseBrowserDefaultLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'])])){
                    $this->scope->SESSION['lang'] = $this->parseBrowserDefaultLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
                }else{
                    $this->scope->SESSION['lang'] = $this->language->getDefaultLanguage();
                }
            }
            $this->language->lang = $this->scope->currentLang = $this->scope->SESSION['lang'];
            $root->language = $this->scope->language = $this->language;
        }

        public function parseBrowserDefaultLanguage($http_accept){
            $deflang = 'en';
            if(isset($http_accept) && strlen($http_accept) > 1){
              # Split possible languages into array
              $x = explode(",",$http_accept);
              foreach ($x as $val) {
                 #check for q-value and create associative array. No q-value means 1 by rule
                 if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$val,$matches))
                    $lang[$matches[1]] = (float)$matches[2];
                 else
                    $lang[$val] = 1.0;
              }
              #return default language (highest q-value)
              $qval = 0.0;
              foreach ($lang as $key => $value) {
                 if ($value > $qval) {
                    $qval = (float)$value;
                    $deflang = $key;
                 }
              }
            }
            return strtolower($deflang);
        }

        public function _ln($key){
            return $this->language->_ln($key);
        }

        public function rpc_clearFlash() {
            $this->scope->SESSION['oxygen-flash-messages'] = array();
        }
        
        public function rpc_getFlash() {
			$limit = 10;
            $flash = $this->scope->SESSION->get('oxygen-flash-messages',array());
			$output = array_slice($flash, 0, $limit);
			$this->scope->SESSION['oxygen-flash-messages'] = array_slice($flash, $limit, count($flash));
			return $output;
			//return $this->scope->SESSION->get('oxygen-flash-messages',array());
        }

        public function rpc_selectLanguege($args){
            if(isset($this->language->languages[$args->lang])){
                $this->scope->SESSION['lang'] = $args->lang;
                $page = $args->page;
                $page = preg_replace("/\?lang\=..&/", "?", $page);
                $page = preg_replace("/&lang\=../", "", $page);
            }
            return $page;
        }

		public function configure($x) {
			$x['public']->Dummy('Public page');
            switch($this->auth->role) {
            case 'admin':
    			$x['files']->Dummy('Files','folder_explore');				
            case 'user':
      			$x['users']->Dummy('Users','user');
            default:
        		$x['{url:any}']->LogonPage('login');
			}
		}

         public function generateClasses() {
            $mainSchema = $this->loadSchemaFor(get_class($this));
            foreach ($this->schemata as $s) {
                $s->initializeModels();
            }
            foreach ($this->schemata as $s) {
                $s->resolveModelDependencies();
            }
            foreach ($this->schemata as $s) {
                $s->generateClasses();
            }
            return implode(',', array_keys($this->schemata));
        }

        public function loadSchemaFor($className) {
            if(!isset($this->schemata[$className])) {
                $this->scope->lib->load('yaml-php/lib/sfYaml.php');
                try {
                    $file = $this->scope->loader->pathFor($className,'schema.yml');
                    $yml = sfYaml::load($file);
                    $s = $this->schemata[$className] = $this->scope->Oxygen_Common_Module_Schema(
                        $yml, 
                        $this, 
                        $className
                    );
                    $s->resolveUsings();
                } catch(Exception $e) {
                    $this->throw_Exception($e->getMessage() . ' in ' . $className);
                }
            }
            return $this->schemata[$className];
        }

        public function rpc_Generate() {
            return $this->generateClasses();
        }
        
        public function rpc_clearCache(){
            apc_clear_cache();
            apc_clear_cache("user");
            return true;
        }

        public function rpc_reflectDB()
        {
            $this->scope->connection->reflectDatabases(true);
        }
	}

?>