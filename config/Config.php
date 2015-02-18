<?
namespace oxygen\config;
    class Config {
        private static $config = null;

        public static function get($key){
            $config = self::getConfigFile();
            if($config === null){
                return null;
            }else{
                if(isset($config[$key])){
                    return $config[$key];
                }else{
                    return null;
                }
            }
        }

        private static function getConfigFile(){
            $configPath = CURRENT_ROOT_PATH.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."global.php";
            if(self::$config === null){
                if(file_exists($configPath)){
                    self::$config = include($configPath);
                }
            }
            return self::$config;
        }
    }



?>