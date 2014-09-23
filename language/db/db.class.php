<?
	class Oxygen_Language_DB extends Oxygen_Language {
		public $db = null, $table = null;

		public function setup($params = array()){
			$this->db = $params['db'];
			$this->table = $params['table'];
		}

		public function updateLang($key, $value, $lang){
			if(isset($this->languages[$lang])){
				$this->langPack[$lang][$key] = $value;
				try{
					$this->scope->connection->runQuery("replace into ".$this->db.".".$this->table." set value='".addslashes($value)."', `key`='".addslashes($key)."', lang='".addslashes($lang)."'");
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
                    $this->scope->connection->runQuery("replace into ".$this->db.".".$this->table." set `key`='".addslashes($key)."', value='".addslashes($key)."', lang='".addslashes($k)."'");
                }
            }
		}

		public function addLang($key, $values=array()){
			$key = strtolower($key);
			foreach($this->langPack as $k=>$v){
				if(!isset($this->langPack[$k][$key])){
					$v[$key] = $this->langPack[$k][$key] = $values[$k];
					$this->scope->connection->runQuery("replace into ".$this->db.".".$this->table." set value='".addslashes($values[$k])."', lang='".addslashes($k)."'");	
				}
			}
		}

		public function loadLang($name){
			$return = array();
			$langArray = $this->scope->connection->runQuery("select * from ".$this->db.".".$this->table." where lang='".addslashes($name)."'")->toArray();
			foreach($langArray as $l){
				$return[$l['key']] = $l['value'];
			}
			return $return;
		}
	}