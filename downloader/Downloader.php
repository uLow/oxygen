<?
namespace oxygen\downloader;

    use oxygen\object\Object;
    use SimpleXMLElement;

    class Downloader extends Object {

        const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1';
        const TIMEOUT = 60;

        public $ch = null;
        public static $options = array(
            'get' => array(
                CURLOPT_USERAGENT => self::USER_AGENT,
                CURLOPT_FAILONERROR => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => self::TIMEOUT,
                CURLOPT_HTTPGET => 1,
                CURLOPT_ENCODING => 'gzip'
            ),
            'post' => array(
                CURLOPT_USERAGENT => self::USER_AGENT,
                CURLOPT_FAILONERROR => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => self::TIMEOUT,
                CURLOPT_POST => 1,
                CURLOPT_ENCODING => 'gzip'
            )
        );


        public function __construct() {
            $this->ch =  curl_init();
        }

        public function post($url, $params, $auth = false)  {
            foreach(self::$options['post'] as $name => $value) {
                curl_setopt($this->ch, $name, $value);
            }
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST,0);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
            if ($auth !== false) {
                curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($this->ch, CURLOPT_USERPWD, $auth['user'].':'.$auth['pass']);
            }
            $result = curl_exec($this->ch);

            if($result === false){
                return curl_error($this->ch);
            }else{
                return $result;
            }
        }

        public function get($url,$params = array()) {
            foreach(self::$options['get'] as $name => $value) {
                curl_setopt($this->ch, $name, $value);
            }
            if (count($params)>0) {
                if (preg_match("/\?/",$url)) {
                    $url .= '&';
                } else {
                    $url .= '?';

                }
                $url .= http_build_query($params);
            }
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($this->ch);
            
            if($result === false){
                return curl_error($this->ch);
            }else{
                return $result;
            }
        }

        public function getSimpleXML($url, $params = array()) {
            $result = trim($this->get($url, $params));
            $result = new SimpleXMLElement($result);
            $this->__assert(
                $result !== false,
                'INVALID XML'
            );
            return $result;
        }

        public function getJSON($url, $params = array(), $method = 'get', $assoc = false) {
            $result = trim($this->{$method}($url, $params));
            $result = json_decode($result, $assoc);
            if ($result === false) {
                throw $this->scope->{'oxygen\\downloader\\excpetion\\Excpetion'}('Invalid JSON');
            }
            return $result;
        }
    }

