<?

	class Oxygen_Controller extends Oxygen_Object
        implements Countable, ArrayAccess, IteratorAggregate
    {

        const PARAM_EXTRACT_REGEXP = '/^_([0-9]+)_([0-9A-Za-z_]+)$/';
        const ARG_EXTRACT_REGEXP = '/([^\/]*)\/?(.*)/';

		const ROUTING_TEMPLATE = '/^(?:{0})?(?P<{1}>.*)$/';
		const ROUTING_REST = '__';

		const INVALID_CLASS_RETRIEVER       = 'Invalid class retriever';
		const ROUTE_PARAM_REDEFINED         = 'Route param redefined';
		const INVALID_PARAM_TYPE            = 'Invalid route parameter type';
		const CONTROLLER_ALREADY_CONFIGURED = 'Controller is already configured';

		const UNWRAP_METHOD = 'getModel';

		private $configured = false;
		private $children   = array();
		private $index      = array();
		private $pattern    = '';

        private $rawArgs = '';
        protected $args = array();

        public $routes = array();
        public $model  = null;
        public $parent = null;

        public $icon = 'bullet_green';
        public $name = '';

        public $hasPaginator = false;
        public $sliceSize = 20;

        public $hasErrors = false;

        public $isCurrent = false;
        public $isActive = false;
        public $child = false;
        public $hasSubMenu = true;
        public $showInMenu = true;

        protected $count  = false;

        protected $route = '';
        protected $path = '';

        protected $sections = array();

        private $components = array();

        private static $implementations = array(
            'Router'             => 'Oxygen_Router',
            'Routes'             => 'Oxygen_Controller_Routes',
            'Configurator'       => 'Oxygen_Controller_Configurator',
            'Controller'         => 'Oxygen_Controller',
            'ControllerSection'  => 'Oxygen_Controller_Section',
            'Dummy'              => 'Oxygen_Controller_Dummy',
            'ChildrenIterator'   => 'Oxygen_Controller_Iterator'
        );

		public function __construct($model = null){
			$this->model = $model;
            $this->name = $this->route;
		}

        public function rpc_oxygenControllerStateRefresh($args){
            foreach($args as $k=>$v){
                if($k!=='component' && $k!=='refresh')
                    $this->args[$k] = $v;
            }
            return $this->embed_($args->component);
        }

        public function hideInMenu(){
            $this->showInMenu = false;
            return $this;
        }

        public function displayInMenu(){
            $this->showInMenu = true;
            return $this;
        }

        public function getModelData() {
            return array();
        }

        public function insertRow($data, $prefix, $table, $raw=false)
        {
            $insertPart = array();
            foreach($data as $k=>$v){
                $insertPart[] = "`".addslashes($k)."`='".addslashes($v)."'";
            }
            if($raw === false){
                return $this->scope->connection->runQuery("
                    insert into ".addslashes($prefix).".".addslashes($table)." SET
                    ".implode(', ', $insertPart)."
                ");
            }else{
                return $this->scope->connection->rawQuery("
                    insert into ".addslashes($prefix).".".addslashes($table)." SET
                    ".implode(', ', $insertPart)."
                ");
            }
        }

        public function getIconSource() {
            return $this->scope->assets->getIcon($this->getIcon());
        }

        public function registerComponent($name, $component) {
            $this->components[$name] = $component;
        }

        public function getComponent($name) {
            if (!isset($this->components[$name])) {
                $name = htmlentities($name, ENT_QUOTES, "UTF-8");
                throw new Exception("Component {$name} is not found");
            }
            return $this->components[$name];
        }

        public function rpc_componentRequest($args) {
            $component = $this->getComponent($args->component);
            $method = 'rpc_'.$args->method;
            if (method_exists($component, $method)) {
                return $component->$method($args->args);
            } else {
                $args->method = htmlentities($args->method, ENT_QUOTES, "UTF-8");
                throw new Exception("Component method {$args->method} is not found");
            }
        }

        public function getIcon() {
            return $this->icon;
        }

        public function getWIcon() {
            return 'asterisk';
        }

        public function setIcon($icon) {
            $this->icon = $icon;
            return $this;
        }

        public static function __class_construct($scope) {
            $scope->registerAll(self::$implementations);
        }

        public function configurePaginator($sliceSize = 20){
            $this->sliceSize = $sliceSize;
            $this->hasPaginator = true;
            
            if(isset($this->args['page'])){
                $this->paginatorPage = $this->args['page'];
                if($this->paginatorPage<1 || $this->paginatorPage>ceil(count($this->model)/$this->sliceSize) || !is_numeric($this->paginatorPage)){
                    $this->paginatorPage = 1;
                }
            }else{
                $this->paginatorPage = 1;
            }
        }

		public function getModel() {
            if($this->hasPaginator){
                return $this->model->slice($this->sliceSize*($this->paginatorPage-1), $this->sliceSize);
            }else{
                return $this->model;
            }
		}

        public function getDefaultView() {
            return 'view';
        }

        public function put() {
            $args = func_get_args();
            $this->put_($this->getDefaultView(),$args);
        }

        public function deactivate() {
            $this->isActive = false;
            $this->isCurrent = false;
            if ($this->parent) $this->parent->deactivate();
        }

        public function activate() {
            $this->isActive = true;
            $this->isCurrent = false;
            if ($this->parent) {
                $this->parent->child = $this;
                return $this->parent->activate();
            } else {
                return $this;
            }
        }

        public function findUp($class, $includeSelf = false) {
            $x = $includeSelf
                ? $this
                : $this->parent
            ;
            while($x) {
                if ($x instanceof $class) return $x;
                $x = $x->parent;
            }
            return null;
        }

        public function findDown($class, $includeSelf = false) {
            $x = $includeSelf
                ? $this
                : $this->child
            ;
            while($x) {
                if ($x instanceof $class) return $x;
                $x = $x->child;
            }
            return null;
        }

        public function getPathToCurrent() {
            $result = array();
            $x = $this;
            while($x) {
                $result[] = $x;
                $x = $x->child;
            }
            return $result;
        }

        public function getPathToRoot() {
            $result = array();
            $x = $this;
            while($x) {
                $result[] = $x;
                $x = $x->parent;
            }
            return $result;
        }

        public function redirect() {
            return null;
        }

        public function handleGet() {
            if (null !== $r = $this->redirect()) {
                return redirectResponse($r);
            } else {
                $first = $this->makeCurrent();
                return htmlResponse(array($first,'put_page_view'));
            }
        }

        public function post() {
            return htmlResponse(array($this,'put_embed_view'));
            $location = $this->go();
            return redirectResponse($location);
        }

        public function rpc_echo($args) {
            return $args;
        }

        public function rpc_class($args) {
            return get_class($this);
        }

        public function handleRPC($method, $args, $client) {
            $name = 'rpc_' . $method;
            if(method_exists($this,$name)) {
                return $result = $this->$name($args, $client);
            } else {
                $method = htmlentities($method, ENT_QUOTES, "UTF-8");
                throw $this->scope->Exception("Remote method $method either not exists or is not allowed");
            }
        }

        public function getStyleColor() {
            $x = md5($this->route);
            return '#'.substr($x,0,6);
        }

        public function handlePost() {
            $SERVER = $this->scope->SERVER;
            if(isset($SERVER['HTTP_X_OXYGEN_RPC'])) {
                $callback = isset($this->scope->GET['callback'])?$this->scope->GET['callback']:null;
                $method = $SERVER['HTTP_X_OXYGEN_RPC'];
                $continuation = $SERVER['HTTP_X_OXYGEN_CONTINUATION'];
                $args = json_decode(file_get_contents('php://input'));
                
                if ($continuation === 'new') {
                    $client = $this->scope->Oxygen_Communication_Client($this, $continuation);
                } else {
                    $client = $this->scope->Oxygen_Communication_Client($this, $continuation, $args->ask->digest, $args->ask->result, $args->ask->error);
                    $args = $args->args;
                }
                try {
                    $result =  $this->handleRPC($method,$args,$client);
                    return rpcResponse(null, $client->end(), $result, $callback);
                } catch (Oxygen_Communication_Token $oct) {
                    return rpcResponse(null, $oct->getData(), null, $callback);
                } catch(Exception $e) {
                    return rpcResponse($e, null, null, $callback);
                }
            } else {
                return $this->post();
            }
        }

        public function handleRequest() {
            $method = $this->scope->SERVER['REQUEST_METHOD'];
            switch($method){
            case 'GET': return $this->handleGet();
            case 'POST': return $this->handlePost();
            default:
                $this->__assert(
                    false,
                    'Unknown method {0}',
                     $method
                );
            }
        }

        public function getCurrent() {
            if($this->isCurrent) {
                $this->__assert($this->isActive, 'Must be active');
                 return $this;
            } if ($this->isActive) {
                $this->__assert($this->child, 'Must have child');
                return $this->child->getCurrent();
            } else {
                if($this->parent) {
                    $this->__assert($this->parent->child !== $this, 'Cyclic activity');
                    return $this->parent->getCurrent();
                } else {
                    $this->isActive = true;
                    $this->isCurrent = true;
                    return $this;
                }
            }

            if ($this->isCurrent)
            $this->__assert($this->child, 'Child must be selected');
            return $this->child->getCurrent();
        }


        public function makeCurrent() {
            $this->getCurrent()->deactivate();
            $root = $this->activate();
            $this->isCurrent = true;
            return $root;
        }

        public function go($path = true, $args = array(), $merge = true) {
            if(is_bool($path)) {
                $p = $this->path === '' ? '/' : $this->path;
                return $path
                    ? $p . $this->rawArgs
                    : $p
                ;
            } else if(is_array($path)) {
                $merge = $args;
                if(is_array($merge)) {
                    $merge = true;
                }
                $args = $path;
                $path = '';
            }
            if ($path === '') {
                $args = $merge
                    ? array_merge($this->args, $args)
                    : $args
                ;
                return $this->path . ((count($args) > 0)
                    ? '&' . http_build_query($args)
                    : ''
                );
            } else {
                $path = (string)$path;
                $args = ((count($args) > 0)
                    ? '&' . http_build_query($args)
                    : ''
                );
                if ($path{0} === '/') {
                    return $this->scope->OXYGEN_ROOT_URI . $path . $args;
                } else {
                    return $this->path . $this->rawArgs . '/' . $path . $args;
                }
            }
        }

        public function __routed() {

        }

		public function routeExists($route) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#', $route, $match)) {;
                switch($route){
                case '': return true;
                case '.': return true;
                case '..': return !$this->isRoot();
                default:
                    return $this->isRoot()
                        ? $this->routeExists($match[4])
                        : $this->parent['/']->routeExists($match[4])
                    ;
                }
            }
            $this->ensureConfigured();
			preg_match($this->pattern, $route, $match);
			$rest = $match[self::ROUTING_REST];
			return $rest != $route;
		}

		public function offsetExists($route) {
			if(isset($this->children[$route])) return true;
			return $this->routeExists($route);
		}

		public function count() {
            if ($this->count === false) {
                $this->ensureConfigured();
                foreach($this->routes as $router) {
                    $this->count += count($router);
                }
                return $this->count;
            } else {
                return $this->count;
            }
		}

		public function getIterator() {
			$this->ensureConfigured();
			return $this->scope->ChildrenIterator($this);
		}

		public function offsetUnset($offset){

		}

        public function __toString() {
            return urldecode($this->getName());
        }

        public function setName($name)
        {
            $this->name = $name;
            return $this;
        }

        public function getName()
        {
            return $this->name;
        }

		public function offsetSet($offset, $value) {
			$this->throw_Exception('Please refer to user manual how to configure controllers');
		}

        public function parseArgs(){
            $this->_rpcArgs = (array)json_decode(file_get_contents('php://input'));

            $array = array();
            parse_str($this->rawArgs, $array);
            $this->args = $array;
            if(isset($this->_rpcArgs, $this->_rpcArgs['refresh'])){
                foreach($this->_rpcArgs as $k=>$v){
                    if($k != 'component' && $k != 'refresh'){
                        $this->args[$k] = $v;
                    }
                }
            }
        }

        public function setPath($parent, $route = '', $rest = ''){
            preg_match(self::ARG_EXTRACT_REGEXP, $rest, $match);
            $this->parent = $parent;
            $this->rawArgs = $match[1];
            $this->route = $route;
            if (is_string($parent)) {
                $path = $parent;
                $this->parent = null;
            } else {
                $path = $parent->path;
                $this->parent = $parent;
            }
            $prevPath = $this->path;
            if($route !== '') $this->path = $path . '/' . $route;
            else $this->path = $path;
            $this->parseArgs();
            $this->__routed();
            // in case if we are rebased - update all existing children;
            if ($prevPath !== $this->path) {
                foreach ($this->children as $route => $child) {
                    $child->setPath($this, $route);
                }
            }
            return $match[2];
        }

        public function isRoot() {
            return $this->parent === null;
        }

        public function offsetGet($offset) {
            if (isset($this->children[$offset])) {
                return $this->children[$offset];
            } else {
                try {
                    return $this->children[$offset] = $this->evalOffset($offset);
                } catch (Oxygen_RedirectException $ex) {
                    throw $ex;
                } catch (Exception $ex) {
                    throw new Exception("Error on accessing route $offset in ".get_class($this). ': '. $ex->getMessage());
                }
            }
        }

        public function setOffsetCache($offset, $value) {
            return $this->children[$offset] = $value;
        }

        public function tryOffsetCache($offset, &$result) {
            if (isset($this->children[$offset])) {
                $result = $this->children[$offset];
                return true;
            } else {
                $result = false;
                return false;
            }
        }

        private function evalOffset($offset) {
            if (preg_match('#^((?:(\.)|(\.\.)|/(.*))(/.*$|$)|$)#',$offset, $match)) {;
                switch($offset){
                case '': return $this;
                case '.': return $this;
                case '..': return $this->isRoot()
                    ? $this->routeMissing('..')
                    : $this->parent
                ;
                default:
                    return $this->isRoot()
                        ? $this[$match[4]]
                        : $this->parent['/'][$match[4]]
                    ;
                }
            }
			$this->ensureConfigured();
            preg_match($this->pattern, $offset, $match);
			$rest = $match[self::ROUTING_REST];
			if ($rest === $offset) return $this->routeMissing($offset);
            $router = null;
            $actual = '';
            foreach($this->index as $index => $route) {
                if(isset($match[$index]) && $match[$index] !== '') {
                    $actual = $match[$index];
                    $router = $this->routes[$route];
                    break;
                }
            }
            $this->__assert($router !== null,'Router is null');
            if(isset($this->children[$actual])) {
                $next = $this->children[$actual];
            } else {
                $next = $router[$actual];
                $this->children[$actual] = $next;
            }
            $rest = $next->setPath($this, $actual, $rest);
            return ($rest === '')
                ? $next
                : $next[$rest]
            ;
		}

		public function routeMissing($route) {
            $this->__assert(false,
				'Route {0} is missing',
				$route
			);
		}

		private function postConfigure() {
			$regexp = '';
			foreach($this->routes as $route){
				if ($regexp != '') $regexp .= '|';
				$regexp .= '(' . $route->getRegexp() . '(?![^&\/]))';
			}
			$this->pattern = Oxygen_Utils_Text::format(
				self::ROUTING_TEMPLATE,
				$regexp,
				self::ROUTING_REST
			);
			$this->configured = true;
		}

        public function setSectionAlias($alias, $route) {
            $this->sections[$alias] = $route;
        }

        public function section($alias, $refinement = false) {
            $this->ensureConfigured();
            try {
                $route = $this->sections[$alias];
                $router = $this->routes[$route];
                if ($refinement !== false) {
                    $router = $router->refine($refinement);
                }
                return $this->scope->ControllerSection($this, $router);
            } catch(Exception $e) {
                throw $this->scope->Exception("Section $alias is not properly configured in class ".get_class($this)." or registered:" . $e->getMessage() . " sections available" . implode(',',array_keys($this->sections)));
            }
        }

        public function addExplicit($route,$model) {
            $index = count($this->routes) + 1;
            $this->index[$index] = $route;
            $this->__assert(
                $model instanceof Oxygen_Controller,
                'Explicit child should be instance of Oxygen_Controller'
            );
            $model->setPath($this,$route);
            return $this->routes[$route] = $this->scope->Router($route, $model);
        }

        public function sectionDefined($name) {
            return isset($this->sections[$name]);
        }

		public function add($class, $route, $model) {
            $index = count($this->routes) + 1;
            $this->index[$index] = $route;
            $router = $this->routes[$route] = $this->scope->Router(
				$route, $model, $class, self::UNWRAP_METHOD
			);
            if($router->type !== Oxygen_Router::SINGLE){
                if(!$this->sectionDefined('data')){
                    $this->setSectionAlias('data', $route);
                }
            }
            return $router;
		}

		public function ensureConfigured() {
			if(!$this->configured){
				$routes = $this->scope->Routes($this);
				$this->configure($routes);
			    $this->postConfigure();
			}
		}

		public function configure($routes) {
        }

        public function requireInt($name,$flash = false) {
            return $this->requirePost($name,'int',$flash);
        }

        public function requireFile($name,$flash = false) {
            return $this->requirePost($name,'file',$flash);
        }

        public function requirePost($name,$type,$flash = false) {

            $x = $type === 'file'
                ? $this->scope->FILES
                : $this->scope->POST
            ;
            
            if($type === 'file' && empty($this->scope->FILES)) {
                $max = ini_get('post_max_size');
                throw new Exception("Your request is too large: MAX=$max ");
            }
            if(isset($x[$name])) {
                $res = $x[$name];
                    if($type === 'file') {
                        $files = array();
                        if ($many = is_array($res['error'])) {
                            foreach($res['name'] as $i=>$_){
                                $files[$i] = array();
                                foreach($res as $prop => $values) {
                                    $files[$i][$prop] = $values[$i];
                                }
                            }
                        } else {
                            $files[] = $res;
                        }
                        foreach ($files as $res) {
                            if(($e = $res['error']) != 0) {
                                $f = $res['name'];
                                $fileErrors = array(
                                    UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
                                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                                );
                                $error = $fileErrors[$e];
                                throw new Exception("Error with file '$f': $error");
                            }
                        }
                        if ($many) {
                            $res = $files;
                        } else {
                            $res = $files[0];
                        }
                    }
                return $res;
            } else if ($flash) {
                throw new Exception($flash);
            } else {
                throw new Exception("Field $name is missing");
            }
        }

        public function flashError($error) {
            $this->hasErrors = true;
            $this->flash($error,"error");
        }


        public function getData($searchCriteria) {
            return array();
        }

        public function getItemsCollection($criteria) {
            if ($criteria === '') return $this;
            $this->ensureConfigured();
            if(isset($this->sections['data'])) {
                return $this->section('data',$this->getData($criteria));
            } else {
                return $this;
            }
        }

        public function getRpcTemplates() {
            return array('node');
        }

        public function rpc_getItems($args) {
            if(in_array($args->nodeTemplate, $this->getRpcTemplates())){
                $nodeTemplate = $args->nodeTemplate;
                $criteria = $args->criteria;
                return $this->embed_nodes($this->getItemsCollection($criteria),$nodeTemplate,$args);
            } else {
                throw new Exception("Template {$args->nodeTemplate} is not allowed from rpc");
            }
        }

        public function putRouteTemplate()
        {
            $args = func_get_args();
            $this->put_($this->route,$args);
        }

        public function isHidden()
        {
            if(isset($this->hidden)){
                return $this->hidden;
            }

            return false;
        }

        public function prev()
        {
            $prev = false;
            foreach($this as $item){
                if($item->isActive){
                    break;
                }
                $prev = $item;
            }
            return $prev;
        }

        public function next()
        {
            $prevActive = false;
            foreach($this as $item){
                if($prevActive && !$item->isActive){
                    return $item;
                }
                $prevActive = $item->isActive;
            }
            return false;
        }

        public function rpc_outterLoad($args)
        {
            return $this->embed_iframe($args->outter_url);
        }
	}

?>