<?
namespace oxygen\logger;
	use Exception;
	use oxygen\config\Oxygen_Config;
	use oxygen\object\Oxygen_Object;
	use oxygen\scope\Oxygen_Scope;

	class Oxygen_Logger extends Oxygen_Object
	{
		private static $logFile;
		private static $path = "";
		private static $loadedFrom = "";

		public function __construct($loadedFrom = false, $logFile = false, $path = "oxygen_logs/")
		{
			/*echo "<pre>";
			die(print_r(debug_backtrace()));*/
			if($loadedFrom === false){
				$loadedFrom = get_class($this);
			}
			$this->loadedFrom = $loadedFrom;
			$this->logFile = $logFile 
				? $logFile 
				: "log_".date("d-m-Y").".log"
			;
			$this->setPath($path);
		}

		public static function setPath($path){
			self::$path = $path;
		}
		public static function getPath(){
			return self::$path;
		}
		public static function setLogFile($logFile){
			self::$logFile = $logFile;
		}
		public static function getLogFile(){
			return self::$logFile;
		}

		public static function writeLog($logText, $path, $logRequest = false){
			$scope = Oxygen_Scope::getRoot();
			$timeStamp = date("[d.m.Y H:i:s]: ");
			self::writeFile(
				$timeStamp
				. " [".self::$loadedFrom."] "
				. $logText,
				$path
			);
			if($logRequest !== false){
				$logRequest = strtoupper($logRequest);
				switch($logRequest){
					case 'POST': 
					case 'GET':
					case 'REQUEST':
					case 'COOKIE': 
						self::writeFile(
							$timeStamp
							. " [".$logRequest."::".self::$loadedFrom."] "
							. json_encode($scope->{$logRequest}),
							$path
						);
					break;
				}
			}
		}

		public static function writeFile($value, $path) {
			$f = fopen($path,'a');
			$ex = null;
			try {
				fwrite($f, $value.PHP_EOL);
			} catch(Exception $e) {
				$ex = $e;
			}
			fclose($f);
			if($ex !== null) {
				throw $ex;
			}
		}

		public static function log($loadedFrom = false, $logText = "", $logFile = false, $logRequest = false, $path = false){
			if($loadedFrom === false){
				$loadedFrom = __CLASS__;
			}
			if($path === false){
				$path = Oxygen_Config::get('log_path');
			}
			self::$loadedFrom = $loadedFrom;
			$logFile = $logFile 
				? $logFile 
				: "log_".date("d-m-Y").".log"
			;
			$path = $path.$logFile;
			self::writeLog($logText, $path, $logRequest);
		}
	}