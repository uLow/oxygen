<?
namespace oxygen\object;

    use Exception;
    use oxygen\dumper\Oxygen_Dumper;
    use oxygen\loader\Oxygen_Loader;
    use oxygen\logger\Oxygen_Logger;
    use oxygen\Oxygen;
    use oxygen\redirect_exception\Oxygen_RedirectException;
    use oxygen\scope\Oxygen_Scope;
    use oxygen\utils\text\Oxygen_Utils_Text;

    class Oxygen_Object {

        const OBJECT_CLASS    = 'Oxygen_Object';
        const EXCEPTION_CLASS = 'Oxygen_Exception';
        const SCOPE_CLASS     = 'Oxygen_Scope';

        const DEFAULT_TO_STRING = '[{0} Object]';
        const ASSERTION_FAILED = 'Assertion failed';

        const CALL_REGEXP = '/^(parent_)?(get_|put_|throw_|new_|embed_)(.*)$/';
        const UNKNOWN_METHOD = 'Unknown method {0}->{1}';

        public $scope = null;
        public $logger = null;
        public $showInMenu = true;

        public function redirectTo($url)
        {
            throw new Oxygen_RedirectException($url);
        }

        public function urlFor($resource) {
            return $this->scope->loader->urlFor(get_class($this),$resource);
        }

        public function pathFor($resource) {
            return $this->scope->loader->pathFor(get_class($this),$resource);
        }

        public function loadYaml($resource) {
            if(!preg_match('/\.ya?ml$/',$resource)) {
                $resource .= '.yml';
            }
            $this->scope->lib->load('yaml-php/lib/sfYaml.php');
            $file = $this->scope->loader->pathFor(get_class($this),$resource);
            return sfYaml::load($file);
        }

        public static function newGuid() {
            $s = '0123456789abcdef';
            $r = '';
            for ($i = 0; $i < 40; $i++) {
                $r .= $s{mt_rand()%16};
            }
            return $r;
        }

        private static function queueFlash($message, $type = 'info') {
            $trace = debug_backtrace();
            $trace = $trace[1];
            $scope = Oxygen_Scope::getRoot();
            $messages = $scope->SESSION->get('oxygen-flash-messages',array());
            $messages[] = array('message'=>$message, 'type'=>$type, 'at' =>
                    str_replace(DIRECTORY_SEPARATOR,'/',
                    str_replace($scope->OXYGEN_ROOT,'',$trace['file'])). ':' . $trace['line']
            );
            $scope->SESSION['oxygen-flash-messages'] = $messages;
        }

        public static function flash($message, $type = 'info') {
            self::queueFlash($message, $type);
        }

        public function flashLog($object){
            $this->queueFlash($object,'debug');
        }

        public function __call($method, $args) {
            $this->__assert(
                preg_match(self::CALL_REGEXP, $method, $match),
                self::UNKNOWN_METHOD,
                get_class($this),
                $method
            );
            $class = get_class($this);
            if ($match[1] !== '') $class = get_parent_class($this);
            return $this->{$match[2]}($match[3],$args);
        }

        public function getDefaultView() {
            return 'view';
        }

        public final function new_($class, $args = array()) {
            return $this->scope->resolve($class)->getInstance($args, $this->scope);
        }

        public final function throw_($class, $args) {
            throw $this->new_($class, $args);
        }

        public final function embed_($name, $args = array(), $class = false) {
            $assets = $this->scope->assets;
            $body = $this->get_($name, $args, $class);
            $less = $assets->less->compile();
            $js   = $assets->js->compile();
            return array(
                'body' => $body,
                'less' => $this->scope->OXYGEN_ROOT_URI . '/' . $less . '.less',
                'js' => $this->scope->OXYGEN_ROOT_URI . '/' . $js . '.js'
            );
        }

        public final function get_($name, $args = array(), $class = false) {
            ob_start();
            try {
                $this->put_($name, $args, $class);
                $ex = null;
            } catch(Exception $e) {
                $ex = $e;
            }
            if ($ex !== null) {
                ob_end_clean();
                throw $ex;
            } else {
                return ob_get_clean();
            }
        }

        public final function put_($name, $args = array(), $class = false) {
            $class = ($class === false) ? get_class($this) : $class;
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
                include $scope->loader->pathFor(
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
        }

        public function getTemplateClass() {
            return Oxygen::getCssClass();
        }


        public function __toString() {
            return Oxygen_Utils_Text::format(self::DEFAULT_TO_STRING, get_class($this));
        }

        public final function __assert(
            $condition,
            $message = false,
            $arg0 = '', $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = ''
        ) {
            if (!$condition) {
                $this->throw_Exception(
                    Oxygen_Utils_Text::format(
                        ($message === false ? self::ASSERTION_FAILED : $message),
                        $arg0, $arg1, $arg2, $arg3, $arg4
                    )
                );
            }
        }

        public function __complete() {
        }

        public static function __class_construct($scope) {
            /* Intentionally left blank. No code here */
        }

        public function __depend($scope) {
            $this->scope = $scope;
        }

        // Small inheritance hack:
        // Let system think that EXCEPTION_CLASS
        // is inherited from OBJECT_CLASS (not from Exception)
        public static function getOxygenParentClass($class) {
            return $class === self::EXCEPTION_CLASS
                ? self::OBJECT_CLASS
                : get_parent_class($class)
            ;
        }

        public static function isOxygenClass($class) {
            return (is_subclass_of($class, self::OBJECT_CLASS)
            || is_subclass_of($class, self::EXCEPTION_CLASS)
            || $class === self::OBJECT_CLASS
            || $class === self::EXCEPTION_CLASS
            );
        }

        public static function log($logText="", $logRequest = false, $logPath = false){
            Oxygen_Logger::log(get_called_class(), $logText, false, $logRequest, $logPath);
        }

        public function _($key){
            return $this->scope->language->_ln($key);
        }

        public function getLang()
        {
            return $this->scope->currentLang;
        }

        public function encodeText($text){
            return htmlentities($text, ENT_QUOTES, 'UTF-8');
        }

        public function getArrayValues($arr){
            /*$restrict = array("scope", "Oxygen_Scope", "Oxygen_Object", "Oxygen_Controller", "Oxygen_Controller");
            $allow = array("Oxygen_SQL_ResultSet", "Oxygen_SQL_Connection_Oracle", "Oxygen_Object");
            $return = array();
            if(is_array($arr) || is_object($arr)){
                foreach($arr as $k=>$v){
                    if(!is_object($v) || (!preg_match("/Oxygen_/i", get_class($v)) || !in_array(get_class($v), $restrict))){
                        $return[$k] = $this->print_r($v);
                    }
                }
            }else{
                $return = $arr;
            }

            return $return;*/
            $return = array();
            foreach($arr as $key=>$value){
                if(is_array($value)){
                    $return[$key] = $this->getArrayValues($value);
                }elseif(is_object($value)){
                    $return[$key] = array(
                        'methods'=>implode(', ', get_class_methods($value)),
                        'properties'=>print_r((array)get_object_vars($value), 1)
                    );
                }else{
                    $return[$key] = in_array(gettype($value), array('integer','string','bool')) ? $value : gettype($value);
                }
            }
            return $return;
        }

        public function print_r($return = false, $styled = true){
            if($styled === true){
                $s1 = '<div style="overflow: auto; height: 100%;"><pre>';
                $s2 = '</pre></div>';
            }else{
                $s1 = '';
                $s2 = '';
            }
            if($return === true){
                return $s1.Oxygen_Dumper::dump($this).$s2;
            }else{
                echo $s1;
                echo Oxygen_Dumper::dump($this);
                echo $s2;
            }
        }

    }