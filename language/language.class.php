<?
	class Oxygen_Language extends Oxygen_Object{
		
		public $lang;
		private $langPack = null;
		public $languages = array("en"=>"English", "ru"=>"Русский", "lv"=>"Latviešu");

		public function __complete()
		{
			$reflection = new ReflectionClass(get_class($this));
			$this->file = $reflection->getFileName();
		}

		public function setup($params = array()){
			$this->getLangPack();
		}

		public function getDefaultLanguage()
        {
            $keys = array_keys($this->languages);
            return array_shift($keys);
        }

        public function getLanguage($key=false){
            if(!isset($this->scope->SESSION['lang'])){
                return $key ? $this->getDefaultLanguage() : $this->languages[$this->getDefaultLanguage()];
            }else{
                return $key ? $this->scope->SESSION['lang'] : $this->languages[$this->scope->SESSION['lang']];
            }
        }

        public function getExactTranslate($key, $lang){
        	//this method meant to be used by translates list, so i
        	$langpack = $this->getLangPack();
        	if(isset($langpack[$lang][$key])){
				return $langpack[$lang][$key];
			}
			return $key;
        }

		public function readString($key){
			$langpack = $this->getLangPack();
			if(isset($langpack[$this->lang][$key])){
				return $langpack[$this->lang][$key];
			}else{
				foreach($this->otherLanguages() as $lang){
					if(isset($lang[$key])){
						return $lang[$key];
					}
				}
			}
			return $key;
		}

		public function writeLang($lang=false){
			if($lang===false){
				foreach($this->langPack as $k=>$v){
					$path = dirname($this->file) . DIRECTORY_SEPARATOR . $k.".php";
					file_put_contents($path, $this->get_write($v));
					//chmod($path, "777");
				}
			}
		}

		public function updateLang($key, $value, $lang){
			if(isset($this->languages[$lang])){
				$this->langPack[$lang][$key] = $value;
				try{
					$path = dirname($this->file) . DIRECTORY_SEPARATOR . $lang.".php";
					file_put_contents($path, $this->get_write($this->langPack[$lang]));
				}catch(Exception $e){
					return $e->getMessage();
				}
				return true;
			}
			return false;
		}

        public function addKey($key){
            $key = strtolower($key);
            foreach($this->langPack as $k=>$v){
                if(!isset($this->langPack[$k][$key])){
                    $v[$key] = $this->langPack[$k][$key] = $key;
                    $path = dirname($this->file) . DIRECTORY_SEPARATOR . $k.".php";
                    file_put_contents($path, $this->get_write($v));
                    //chmod($path, "777");
                }
            }
        }

        /*
        * $values - array with key(lang) & value
        */
		public function addLang($key, $values=array()){
			$key = strtolower($key);
			foreach($this->langPack as $k=>$v){
				if(!isset($this->langPack[$k][$key])){
					$v[$key] = $this->langPack[$k][$key] = $values[$k];
					$path = dirname($this->file) . DIRECTORY_SEPARATOR . $k.".php";
					file_put_contents($path, $this->get_write($v));
					//chmod($path, "777");
				}
			}
		}

		//readString alias
		public function _ln($key){
			return $this->readString($key);
		}

		public function getLangPack()
		{
			if($this->langPack===null){
				foreach($this->languages as $lang=>$langName){
					$this->langPack[$lang] = $this->loadLang($lang);
				}
			}
			return $this->langPack;
		}

		public function getLangArray($lang=false){
			if($lang===false){
				return $this->langPack[$this->scope->SESSION['lang']];
			}else{
				return $this->langPack[$lang];
			}
		}

		public function otherLanguages(){
			$langPack = $this->getLangPack();
			unset($langPack[$this->lang]);
			return $langPack;
		}

		public function loadLang($name){
			$class = get_class($this);
            $call = (object)array(
                'instance'  => $this,
                'class'     => $class,
                'name'      => $name,
                'stack'     => array(),
                'sp'        => 0,
                'component' => false,
                'assets'    => array()
            );
            Oxygen::push($call);
            $scope = $this->scope;
            $assets = $scope->assets;
            $resource = (strpos($name,'.') === false)
                ? $name . Oxygen_Loader::TEMPLATE_EXTENSION
                : $name
            ;
            try {
                $return = include $scope->loader->pathFor(
                    $class,
                    $resource
                );
                $ex = null;
            } catch(Exception $e){
                $ex = $e;
            }
            Oxygen::closeAll();
            $result = Oxygen::pop();
            if ($ex !== null) throw $ex;
            $assets->add($result);

            return $return;
		}
	}