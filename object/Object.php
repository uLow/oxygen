<?php
namespace oxygen\object;

use Exception;
use oxygen\dumper\Dumper;
use oxygen\loader\Loader;
use oxygen\logger\Logger;
use oxygen\Oxygen;
use oxygen\redirect_exception\RedirectException;
use oxygen\scope\Scope;
use oxygen\utils\text\Text;


class Object
{

    const OBJECT_CLASS = 'oxygen\\object\\Object';
    const EXCEPTION_CLASS = 'oxygen\\exception\\Exception';
    const SCOPE_CLASS = 'oxygen\\scope\\Scope';

    const DEFAULT_TO_STRING = '[{0} Object]';
    const ASSERTION_FAILED = 'Assertion failed';

    const CALL_REGEXP = '/^(parent_)?(get_|put_|throw_|new_|embed_)(.*)$/';
    const UNKNOWN_METHOD = 'Unknown method {0}->{1}';

    /* @var \oxygen\scope\Scope $scope */
    public $scope = null;
    public $logger = null;
    public $showInMenu = true;

    /**
     * @return string
     */
    public static function newGuid()
    {
        $s = '0123456789abcdef';
        $r = '';
        for ($i = 0; $i < 40; $i++) {
            $r .= $s{mt_rand() % 16};
        }
        return $r;
    }

    /**
     * @param $message
     * @param string $type
     */
    public static function flash($message, $type = 'info')
    {
        self::queueFlash($message, $type);
    }

    /**
     * @param $message
     * @param string $type
     */
    private static function queueFlash($message, $type = 'info')
    {
        $trace = debug_backtrace();
        $trace = $trace[1];
        $scope = Scope::getRoot();
        $messages = $scope->SESSION->get('oxygen-flash-messages', array());
        $messages[] = array('message' => $message, 'type' => $type, 'at' =>
            str_replace(DIRECTORY_SEPARATOR, '/',
                str_replace($scope->OXYGEN_ROOT, '', $trace['file'])) . ':' . $trace['line']
        );
        $scope->SESSION['oxygen-flash-messages'] = $messages;
    }

    /**
     * @param Scope $scope
     */
    public static function __class_construct($scope)
    {
        /* Intentionally left blank. No code here */
    }

    /**
     * @param object|string $class
     * @return string
     */
    public static function getOxygenParentClass($class)
    {
        return $class === self::EXCEPTION_CLASS
            ? self::OBJECT_CLASS
            : get_parent_class($class);
    }

    /**
     * @param object|string $class
     * @return bool
     */
    public static function isOxygenClass($class)
    {
        return (is_subclass_of($class, self::OBJECT_CLASS)
            || is_subclass_of($class, self::EXCEPTION_CLASS)
            || $class === self::OBJECT_CLASS
            || $class === self::EXCEPTION_CLASS
        );
    }

    /**
     * @param string $logText
     * @param bool|string $logRequest
     * @param bool|string $logPath
     */
    public static function log($logText = "", $logRequest = false, $logPath = false)
    {
        Logger::log(get_called_class(), $logText, false, $logRequest, $logPath);
    }

    public function redirectTo($url)
    {
        throw new RedirectException($url);
    }

    public function urlFor($resource)
    {
        return $this->scope->loader->urlFor(get_class($this), $resource);
    }

    public function pathFor($resource)
    {
        return $this->scope->loader->pathFor(get_class($this), $resource);
    }

    public function loadYaml($resource)
    {
        if (!preg_match('/\.ya?ml$/', $resource)) {
            $resource .= '.yml';
        }
        $this->scope->lib->load('yaml-php/lib/sfYaml.php');
        $file = $this->scope->loader->pathFor(get_class($this), $resource);
        return \sfYaml::load($file);
    }

    public function flashLog($object)
    {
        $this->queueFlash($object, 'debug');
    }

    public function __call($method, $args)
    {
        $this->__assert(
            preg_match(self::CALL_REGEXP, $method, $match),
            self::UNKNOWN_METHOD,
            get_class($this),
            $method
        );
        $class = get_class($this);
        if ($match[1] !== '') $class = get_parent_class($this);
        return $this->{$match[2]}($match[3], $args);
    }

    public final function __assert(
        $condition,
        $message = false,
        $arg0 = '', $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = ''
    )
    {
        if (!$condition) {
            $this->throw_Exception(
                Text::format(
                    ($message === false ? self::ASSERTION_FAILED : $message),
                    $arg0, $arg1, $arg2, $arg3, $arg4
                )
            );
        }
    }

    public function getDefaultView()
    {
        return 'view';
    }

    public final function throw_($class, $args)
    {
        throw $this->new_($class, $args);
    }

    public final function new_($class, $args = array())
    {
        if (!is_object($this->scope)) {
            //echo $class.' has no scope';
            $this->scope = Scope::getRoot();
        }
        return $this->scope->resolve($class)->getInstance($args, $this->scope);
    }

    public final function embed_($name, $args = array(), $class = false)
    {
        $assets = $this->scope->assets;
        $body = $this->get_($name, $args, $class);
        $less = $assets->less->compile();
        $js = $assets->js->compile();
        return array(
            'body' => $body,
            'less' => $this->scope->OXYGEN_ROOT_URI . '/' . $less . '.less',
            'js' => $this->scope->OXYGEN_ROOT_URI . '/' . $js . '.js'
        );
    }

    public final function get_($name, $args = array(), $class = false)
    {
        ob_start();
        try {
            $this->put_($name, $args, $class);
            $ex = null;
        } catch (Exception $e) {
            $ex = $e;
        }
        if ($ex !== null) {
            ob_end_clean();
            throw $ex;
        } else {
            return ob_get_clean();
        }
    }

    /**
     * @param $name
     * @param array $args
     * @param string|bool $class
     * @throws Exception
     * @throws null
     */
    public final function put_($name, $args = array(), $class = false)
    {
        $class = ($class === false) ? get_class($this) : $class;
        $call = (object)array(
            'instance' => $this,
            'class' => $class,
            'name' => $name,
            'stack' => array(),
            'sp' => 0,
            'component' => false,
            'assets' => array()
        );
        Oxygen::push($call);
        $scope = Scope::getRoot();
        $assets = $scope->assets;
        $resource = (strpos($name, '.') === false)
            ? $name . Loader::TEMPLATE_EXTENSION
            : $name;
        try {
            include $scope->loader->pathFor(
                $class,
                $resource
            );
            $ex = null;
        } catch (Exception $e) {
            $ex = $e;
        }
        Oxygen::closeAll();
        $result = Oxygen::pop();
        if ($ex !== null) throw $ex;
        $assets->add($result);
    }

    public function getTemplateClass()
    {
        return Oxygen::getCssClass();
    }

    // Small inheritance hack:
    // Let system think that EXCEPTION_CLASS
    // is inherited from OBJECT_CLASS (not from Exception)

    public function __toString()
    {
        return Text::format(self::DEFAULT_TO_STRING, get_class($this));
    }

    public function __complete()
    {
    }

    public function __depend($scope)
    {
        $this->scope = $scope;
    }

    public function _($key)
    {
        return $this->scope->language->_ln($key);
    }

    public function getLang()
    {
        return $this->scope->currentLang;
    }

    public function encodeText($text)
    {
        return htmlentities($text, ENT_QUOTES, 'UTF-8');
    }

    public function getArrayValues($arr)
    {
        $return = array();
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $return[$key] = $this->getArrayValues($value);
            } elseif (is_object($value)) {
                $return[$key] = array(
                    'methods' => implode(', ', get_class_methods($value)),
                    'properties' => print_r((array)get_object_vars($value), 1)
                );
            } else {
                $return[$key] = in_array(gettype($value), array('integer', 'string', 'bool')) ? $value : gettype($value);
            }
        }
        return $return;
    }

    public function print_r($return = false, $styled = true)
    {
        if ($styled === true) {
            $s1 = '<div style="overflow: auto; height: 100%;"><pre>';
            $s2 = '</pre></div>';
        } else {
            $s1 = '';
            $s2 = '';
        }
        if ($return === true) {
            return $s1 . Dumper::dump($this) . $s2;
        } else {
            echo $s1;
            echo Dumper::dump($this);
            echo $s2;
        }
    }

}