<?
    class Oxygen_Geoip extends Oxygen_Object {
		public $geoipaddrfrom = null;
		public $geoipaddrupto = null;

		public $geoipctry = null;
		public $geoipcntry = null;
		public $geoipcountry = null;

		public function init(){
			if($this->geoipaddrfrom === null){
				$this->geoipaddrfrom = include $this->pathFor('from.php');
			}
			if($this->geoipaddrupto === null){
				$this->geoipaddrupto = include $this->pathFor('to.php');
			}
			if($this->geoipctry === null){
				$this->geoipctry = include $this->pathFor('iso2.php');
			}
			if($this->geoipcntry === null){
				$this->geoipcntry = include $this->pathFor('iso3.php');
			}
			if($this->geoipcountry === null){
				$this->geoipcountry = include $this->pathFor('iso2_full.php');
			}
		}

		// can use direct hash because max # possible IPAddress = max size of cache array
		// realistically, cache size will be much much smaller

		public function getCountryFromIP($ip = false, $type = "name"){
			$geoipcount = count($this->geoipaddrfrom);
			$geoipcache = array();

			if(!$ip){
				if(isset($_SERVER['HTTP_CLIENT_IP'])){
					$ip = $_SERVER['HTTP_CLIENT_IP'];
				}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}else{
					$ip = $_SERVER['REMOTE_ADDR'];
				}
			}

			if(strpos($ip, ".") === false){
				return "";
			}

			$ip = substr("0000000000" . sprintf("%u", ip2long($ip)), -10);
			$ipn = base64_encode($ip);

			if(isset($geoipcache[$ipn])){ // search in cache
				$ct = $geoipcache[$ipn];
			}else{ // search in IP Address array
				$from = 0;
				$upto = $geoipcount;
				$ct   = "ZZ"; // default: Reserved or Not Found

				// simple binary search within the array for given text-string within IP range
				while($upto > $from){
					$idx = $from + intval(($upto - $from)/2);
					$loip = substr("0000000000" . $this->geoipaddrfrom[$idx], -10);
					$hiip = substr("0000000000" . $this->geoipaddrupto[$idx], -10);

					if($loip <= $ip && $hiip >= $ip){
						$ct = $this->geoipctry[$idx];
						break;
					}else if($loip > $ip){
						if($upto == $idx){
							break;
						}
						$upto = $idx;
					}else if($hiip < $ip){
						if($from == $idx){
							break;
						}
						$from = $idx;
					}
				}

				// cache the country code
				$geoipcache[$ipn] = $ct;
			}

			$type = trim(strtolower($type));

			if($type == "abbr"){
				$ct = $this->geoipcntry[$ct];
			}else if($type == "name"){
				$ct = $this->geoipcountry[$ct];
			}

			return $ct;
		}
	}