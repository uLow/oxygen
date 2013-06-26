<?

    require_once "object/object.class.php";
    require_once "loader/loader.class.php";
    require_once "scope/scope.class.php";
    require_once "factory/factory.class.php";
    require_once "factory/class/class.class.php";

    define('OXYGEN_JSON_RESPONSE',1);
    define('OXYGEN_JSONP_RESPONSE',6);
    define('OXYGEN_TEXT_RESPONSE',2);
    define('OXYGEN_HTML_RESPONSE',3);
    define('OXYGEN_XML_RESPONSE',4);
    define('OXYGEN_REDIRECT_RESPONSE',5);
    define('OXYGEN_DOWNLOAD_RESPONSE',7);
    define('OXYGEN_PDF_RESPONSE',7);

    function o($tag = 'div', $data=array()) {
        if(is_array($tag)) {
            $data = $tag;
            $tag = 'div';
        }
        if($tag{0}=='/'){
            Oxygen::close();
        } else {
            Oxygen::open($tag, $data);
        }
    }

    function rpcResponse($error, $ask, $data, $callback) {
        $error = $error ? $error->getMessage() : null;
        $resp = json_encode(array(
            'data'    => $data,
            'ask'     => $ask,   // Oxygen Communication Token
            'error'   => $error
        ));
        return array(
            'header' => 'Content-Type: text/javascript; Charset=UTF-8',
            'type'   => OXYGEN_JSONP_RESPONSE,
            'body'   => $callback.'('. $resp . ')'
        );
    }

    function jsonResponse($data, $headers = array()) {
    	return array(
			'header' => 'Content-Type: application/json; Charset=UTF-8',
			'type'    => OXYGEN_JSON_RESPONSE,
			'body'    => $data
    	);
    }

    function htmlResponse($data) {
    	return array(
			'header'  =>'Content-Type: text/html; Charset=UTF-8',
			'type'    => OXYGEN_HTML_RESPONSE,
			'body'    => $data
    	);
    }


    function xmlResponse($data) {
    	return array(
			'header'  => 'Content-Type: application/xml; Charset=UTF-8',
			'type'    => OXYGEN_XML_RESPONSE,
			'body'    => $data
    	);
    }

    function textResponse($data) {
    	return array(
			'header'  => 'Content-Type: text/plain; Charset=UTF-8',
			'type'    => OXYGEN_TEXT_RESPONSE,
			'data'    => $data
    	);
    }

    function redirectResponse($data) {
        return array(
            'header'  => 'Location:' . $data,
            'type'    => OXYGEN_REDIRECT_RESPONSE,
            'data'    => false
        );
    }

    function downloadResponse($data, $filename='file') {
        $headers = array(
            'Pragma: public', 
            'Content-Type: application/force-download', 
            'Content-Disposition: inline; filename="'.$filename.'"'
        );
        return array(
            'header'  => $headers,
            'type'    => OXYGEN_DOWNLOAD_RESPONSE,
            'body'    => $data
        );
    }

    function pdfResponse($data, $length=0, $filename='file.pdf') {
        $headers = array(
            'Content-Description: File Transfer', 
            'Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1', 
            'Pragma: public', 
            'Expires: Sat, 26 Jul 1997 05:00:00 GMT', 
            'Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT', 
            'Content-Type: application/force-download', 
            '*Content-Type: application/octet-stream', 
            '*Content-Type: application/download', 
            '*Content-Type: application/pdf', 
            'Content-Disposition: attachment; filename="'.$filename.'";',
            'Content-Transfer-Encoding: binary'
        );
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            header('Content-Length: '.$length);
        }
    	return array(
			'header'  => $headers,
			'type'    => OXYGEN_PDF_RESPONSE,
			'body'    => $data
    	);
    }

    function registerOxygenCommons($scope) {
        $array = array(
            'LogonPage' => 'Oxygen_Common_LogonPage',
            'Authenticator' => 'Oxygen_Common_Auth',
            'Application' => 'Oxygen_Common_Application',
            'Page' => 'Oxygen_Common_Page'
        );
        foreach($array as $name => $class) {
            $scope->register($name, $class);
        }
    }

    class Oxygen_RedirectException extends Exception {
        public $url = '';
        public function __construct($url)
        {
            $this->url = $url;
        }
    }


    function handleHttpRequest($scope, $root, $model = false, $debug = true) {
        $scope->__setEnvironment(array(
            'SERVER'    => $_SERVER,
            'REQUEST'   => $_REQUEST,
            'ENV'       => $_ENV,
            'COOKIE'    => $_COOKIE,
            'POST'      => $_POST,
            'GET'       => $_GET,
            'FILES'     => $_FILES,
            'SESSION'   => $scope->Session()
        ));
        try {
            if ($scope->assets->handled($scope->OXYGEN_PATH_INFO)) exit;
            registerOxygenCommons($scope);
            $userScope = $scope->Scope();
            $root = $userScope->$root($model);

            $scope->httpStatus = 200;
            $scope->httpHeaders = array();
            $root->setPath($scope->OXYGEN_ROOT_URI);
            try{
                $last = $root[$scope->OXYGEN_PATH_INFO];
                $result = $last->handleRequest();
            }catch(Oxygen_RedirectException $e){
                $result = $e->url;
            }
            if (is_string($result)) {
                if($result === '') $result=$scope->SERVER['REQUEST_URI'];
                $result = redirectResponse($result);
            }
            if(is_array($result['header'])){
                foreach($result['header'] as $h) {
                    if(preg_match("/^\*/", $h)){
                        header(substr($h, 1), false);
                    }else{
                        header($h);
                    }
                }
            }else{
                header($result['header']);
            }
            foreach($scope->httpHeaders as $h) {
                header($h);
            }
            if(isset($result['body'])) {
                $body = $result['body'];
                if($body) {
                    if(is_string($body)) echo $body;
                    else call_user_func($body);
                }
            }
        } catch(Exception $ex) {
            if ($debug) {
                try {
                    $scope->__wrapException($ex)->put_view();
                } catch(Exception $ex) {
                    echo $ex->getMessage();
                    //print_r($ex);
                }
            } else {
                //header('HTTP/1.0 500 OxygenError');
                //header('HTTP/1.0 404 Page not found');    
                $root->put_page_view();
                //$root->put_page_view(array('error'=>array('errorText'=>'This page either does not exist or you have no access to it.','errorCode'=>'We are sorry, the page you requested cannot be found.')));
                //echo $ex->getMessage();
            }
        }
    }

    function dbg(){
        ob_clean();
        $args = func_get_args();
        echo "<pre>";
        foreach($args as $arg){
            print_r($arg);
            echo PHP_EOL;
        }
        echo "</pre>";
        exit;
    }

    function getArrayVals($arr){
        $restrict = array("scope", "Oxygen_Scope", "Oxygen_Object", "Oxygen_Controller", "Oxygen_Controller");
        $allow = array("Oxygen_SQL_ResultSet", "Oxygen_SQL_Connection_Oracle", "Oxygen_Object");
        $return = array();
        if(is_array($arr) || is_object($arr)){
            foreach($arr as $k=>$v){
                if(!is_object($v) || (!preg_match("/Oxygen_/i", get_class($v)) || !in_array(get_class($v), $restrict))){
                    $return[$k] = getArrayVals($v);
                }
            }
        }else{
            $return = $arr;
        }
        return $return;
    }

    function _dbg(){
        ob_clean();
        $args = func_get_args();
        echo "<pre>";
        foreach($args as $arg){
            print_r(getArrayVals($arg));
        }
        echo "</pre>";
        exit;
    }

    function encodeText($text){
        return htmlentities($text, ENT_QUOTES, 'UTF-8');
    }

    return Oxygen_Scope::newRoot(dirname(dirname(__FILE__)));

?>
