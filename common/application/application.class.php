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

        public function configureLanguages($languageClass='Oxygen_Language')
        {
            $root = Oxygen_Scope::getRoot();
            $this->scope->register('Language',$languageClass);
            $this->language = $this->scope->Language();
            if(isset($_GET['lang']) && isset($this->language->languages[$_GET['lang']])){
                $this->scope->SESSION['lang'] = $_GET['lang'];
            }else{
                // set language according to country
                $geoIP = $this->scope->Oxygen_Geoip();
                $geoCountry = $geoIP->getCountryFromIP();
                $geoLang = "en";
                switch ($geoCountry) {
                    case 'Latvia':
                    case 'Reserved':
                        $geoLang = "lv";
                        break;
                    case 'Armenia':
                    case 'Azerbaijan':
                    case 'Belarus':
                    case 'Georgia':
                    case 'Kazakhstan':
                    case 'Uzbekistan':
                    case 'Moldova Republic of':
                    case 'Tajikistan':
                    case 'Turkmenistan':
                    case 'Ukraine':
                    case 'Russian Federation':
                    case 'Kyrgyzstan':
                        $geoLang = "ru";
                        break;
                    default:
                        $geoLang = "en";
                        break;
                }
                $this->scope->SESSION['lang'] = $geoLang;
            }
            if(!isset($this->scope->SESSION['lang']) or !isset($this->language->languages[$this->scope->SESSION['lang']])){
                $this->scope->SESSION['lang'] = $this->language->getDefaultLanguage();
            }
            $this->language->lang = $this->scope->currentLang = $this->scope->SESSION['lang'];
            $root->language = $this->scope->language = $this->language;
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
                $page = preg_replace("/\?lang\=../", "", $page);
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