<?
namespace oxygen\scope;

use oxygen\factory\factory_handler\FactoryHandler;
use oxygen\loader\Loader;
use oxygen\object\Object;
use oxygen\registry\Registry;
use ReflectionMethod;

class Scope extends Object
{

    const FACTORY_REDEFINED = 'Factory {0} is redefined in this scope';

    const STATIC_CONSTRUCTOR = '__class_construct';

    private $entries = array();
    private $introduced = array();
    private static $root = null;

    public $namespaces = array();
    protected $parent = null;


    private static $defaultEnvironment = array(
        'TMP_DIR' => '/tmp',
        'SERVER' => array(),
        'SESSION' => array(),
        'COOKIE' => array(),
        'REQUEST' => array(),
        'FILES' => array(),
        'ENV' => array(),
        'GET' => array(),
        'POST' => array()
    );

    public static function __class_construct($scope)
    {
        $scope->null = null;
        $scope->assets = $scope->AssetManager();
        $scope->lib = $scope->LibraryManager();
    }

    public static function getRoot()
    {
        return self::$root;
    }

    public static function detectTempPath()
    {
        if (stristr(PHP_OS, 'WIN')) {
            return 'C://Windows/Temp';
        } else {
            return '/tmp';
        }
    }

    public function throw_Exception($message)
    {
        throw $this->Exception($message);
    }

    private static $implementations = array(
        'InstanceFactory' => 'oxygen\\factory\\instance\\Instance',
        'CallableFactory' => 'oxygen\\factory\\callback\\Callback',
        'Exception' => 'oxygen\\exception\\Exception',
        'ExceptionWrapper' => 'oxygen\\exception\\wrapper\\Wrapper',
        'Scope' => 'oxygen\\scope\\Scope',
        'AssetManager' => 'oxygen\\asset\\manager\\Manager',
        'Session' => 'oxygen\\session\\Session',
        'File' => 'oxygen\\cache\\file\\File',
        'LibraryManager' => 'oxygen\\lib\\Lib',
        // 'ClassFactory'     => 'Oxygen_Factory_Class'  -- REGISTERED AUTOMATICALLY
    );

    public function __depend($scope)
    {
        $this->scope = $this;
        $this->parent = $scope;
    }

    private function __assertFreshName($name)
    {
        $this->__assert(
            !isset($this->entries[$name]) && !isset($this->namespaces[$name]),
            self::FACTORY_REDEFINED,
            $name
        );
    }

    public function __callable($name, $callable)
    {
        $this->__assertFreshName($name);
        return $this->entries[$name] = $this->CallableFactory($callable);
    }

    public function __introduce($class)
    {
        if (self::isOxygenClass($class)
            && !isset($this->introduced[$class])
        ) {
            $this->__introduce(self::getOxygenParentClass($class));
            $constructor = new ReflectionMethod($class, self::STATIC_CONSTRUCTOR);
            $this->introduced[$class] = true;
            if ($constructor->getDeclaringClass()->getName() === $class) {
                call_user_func(array($class, self::STATIC_CONSTRUCTOR), $this);
            }
        }
    }

    public function registerAll($entries)
    {
        foreach ($entries as $name => $class) {
            $this->register($name, $class);
        }
    }

    public function register($name, $class)
    {
        $this->__assertFreshName($name);
        if (!isset($this->namespaces[$name])) {
            $this->namespaces[$name] = $class;
        }
        return $this->entries[$name] = $this->ClassFactory($class);
    }

    public function getClassFullName($className)
    {
        // check if already namespace
        if (preg_match('#\\\#', $className)) {
            return $className;
        } else {
            if (isset($this->namespaces[$className])) {
                return $this->getClassFullName($this->namespaces[$className]);
            }
        }
        throw new \Exception('Cannot find namespace for ' . $className);
    }

    public function instance($name, $instance)
    {
        $this->__assertFreshName($name);
        if (!isset($this->namespaces[$name])) {
            $this->namespaces[$name] = $instance;
        }
        return $this->entries[$name] = $this->InstanceFactory($instance);
    }

    public function resolve($name, $autoregister = true)
    {
        if(isset($this->entries[$name])){
            return $this->entries[$name];
        } else if($this->parent !== $this) {
            return $this->entries[$name] = $this->parent->resolve($name, $autoregister);
        } else {
            $this->__assert($autoregister,'Scoped element {0} is not found', $name);
            return $this->register($name,$name);
        }
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $errfiles = explode(DIRECTORY_SEPARATOR, $errfile);
        $errfile = end($errfiles);
        throw $this->Exception("Error($errno) $errstr in $errfile at line $errline");
    }

    public function strictMode()
    {
        set_error_handler(array($this, 'handleError'));
    }

    public function has($name, $recursive = true)
    {
        if (isset($this->entries[$name])) {
            return true;
        } else if ($recursive && $this->parent !== $this) {
            return $this->parent->has($name);
        } else {
            return false;
        }
    }

    public function __get($name)
    {
        if(Registry::isRegistred($name)){
            $name = Registry::getFullName($name);
        }
        return $this->resolve($name, false)->getDefinition();
    }

    public function __set($name, $value)
    {
        $this->instance($name, $value);
    }

    public function __isset($name)
    {
        try {
            $this->resolve($name, false);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __call($name, $args)
    {
        return $this->resolve($name, true)->getInstance($args, $this);
    }

    // Wraps given $exception into Wrapper
    // unless $exception is instance of Exception itself
    public function __wrapException($exception)
    {
        //error_log($exception->getMessage(), 3, '/tmp/php_errors.log');
        if ($exception instanceof \Exception) {
            return $exception;
        } else {
            return $this->ExceptionWrapper($exception);
        }
    }

    public function __setPaths()
    {
        $oxygen = $this->OXYGEN_ROOT;
        if (isset($this->SERVER['DOCUMENT_ROOT'])) {
            //works on test
            //$oxygeRealPath = CURRENT_ROOT_PATH;
            $oxygeRealPath = realpath("oxygen/..");//prod edition
            //$root = $this->SERVER['DOCUMENT_ROOT'];
            $root = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $oxygeRealPath), '/');
        } else {
            $root = '';
        }
        if (isset($this->SERVER['REQUEST_URI'])) {
            $request = $this->SERVER['REQUEST_URI'];
            $request = $root . str_replace('/', DIRECTORY_SEPARATOR, $request);
        } else {
            $request = $oxygen;
        }
        $oxylen = strlen($oxygen);
        $this->__assert(
            substr($request, 0, $oxylen) === $oxygen,
            'Invalid oxygen path [' . $request . ' ' . $oxygen . ']'
        );
        $oxygenRootURI = str_replace(DIRECTORY_SEPARATOR, '/', substr($oxygen, strlen($root)));
        $oxygenURI = str_replace(DIRECTORY_SEPARATOR, '/', substr($request, strlen($oxygen)));
        $q = strpos($oxygenURI, '?');
        if ($q !== false) {
            $oxygenPath = substr($oxygenURI, 0, $q);
            $qs = substr($oxygenURI, $q + 1);
        } else {
            $oxygenPath = $oxygenURI;
            $qs = '';
        }

        $this->DOCUMENT_ROOT = $root;
        $this->OXYGEN_ROOT_URI = $oxygenRootURI;
        $this->OXYGEN_URI = $oxygenURI;
        $this->OXYGEN_PATH_INFO = $oxygenPath;
        $this->QUERY_STRING = $qs;
        // die("<pre>".print_r(array($this->DOCUMENT_ROOT,$this->OXYGEN_ROOT_URI,$this->OXYGEN_URI,$this->OXYGEN_PATH_INFO,$this->QUERY_STRING),1)."</pre>");
    }

    public function __setAssets()
    {
        $this->assets->register('css', 'oxygen\\asset\\css\\Css');
        $this->assets->register('less', 'oxygen\\asset\\less\\Less');
        $this->assets->register('js', 'oxygen\\asset\\js\\Js');
    }

    public function __bootstrap($root)
    {

        $this->__depend($this);
        $this->__complete();

        $factory = new FactoryHandler('oxygen\\factory\\factory_handler\\FactoryHandler');
        $factory->__depend($this);
        $factory->__complete();

        $loader = new Loader($root);
        $loader->__depend($this);
        $loader->__complete();

        $loader->register();
        $this->entries['ClassFactory'] = $factory;

        $this->registerAll(self::$implementations);

        $this->loader = $loader;
        $this->OXYGEN_ROOT = $root;

        $this->__introduce(get_class($this));
        $this->__introduce('oxygen\\factory\\factory_handler\\FactoryHandler');
        $this->__introduce('oxygen\\loader\\Loader');

        return $this;
    }

    public static function newRoot($root)
    {
        $scope = new Scope();
        self::$root = $scope->__bootstrap($root);
        return self::$root;
    }

    public function __authenticated()
    {
        if (!$this->has('auth')) {
            $root = self::getRoot();
            $root->auth = $this->Authenticator();
        }
        return $this->auth;
    }

    public function __setEnvironment($env)
    {
        $env = array_merge(self::$defaultEnvironment, $env);
        $temp = $this->TMP_DIR = self::detectTempPath();//$env['TMP_DIR'];
        $this->SERVER = $env['SERVER'];
        $this->SESSION = $env['SESSION'];
        $this->REQUEST = $env['REQUEST'];
        $this->GET = $env['GET'];
        $this->POST = $env['POST'];
        $this->COOKIE = $env['COOKIE'];
        $this->FILES = $env['FILES'];
        $this->ENV = $env['ENV'];

        $this->register('Cache', 'oxygen\\cache\\file\\File');
        $this->register('Connection', 'oxygen\\sql\\connection\\mysql\\Mysql');

        $this->__setPaths();
        $this->cache = $this->Cache($temp);
        $this->__setAssets();
    }


}


?>