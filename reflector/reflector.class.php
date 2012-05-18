<?
	class Oxygen_Reflector {
		
		public $name = '';
		public $factory = null;
		private $setScope = 'setScopeNone';
		private $setScopeName = 'scope';

		private static $defaults = array(
			'factory'  => false,
			'complte'  => '__complete',
			'setScope' => true,
		);

		public function __construct($name) {
			$this->name = $name;
			$ref = $this->reflected = new ReflectionClass($name);
			try {
				$info = $ref->getMethod('__oxygen_info');
				if (!$info->isStatic()) $info = false;
			} catch(ReflectionException $e) {
				$info = false;
			}
			if ($info !== false) {
				$params = self::$defaults;
				$info->invoke(null, $params);
				$this->factory = $params['factory'] === false
					? array($this->reflected, 'newInstance');
					: $params['factory']
				;
				$setScope = $params['setScope'];
				if ($setScope === true) {
					$this->setScope = 'setScopeDefault'
				} else if ($setScope === false) {
					$this->setScope = 'setScopeNone'
				} else if (preg_match('/^(\\$?)([A-Za-z_][A-Za-z0-9_]*)$/', $setScope, $match)) {
					$this->setScope = $match[1] === '$'
						? 'setScopeVar'
						: 'setScopeMethod'
					;
					$this->setScopeName = $match[2];
				} else {
					$this->setScope = 'setScopeThrow';
					$this->setScopeName = $setScope;
				}
			} else {
				$this->factory = array($this->reflected, 'newInstance');
			}
		}

		private function setScopeDefault($obj, $scope) {
			$obj->scope = $scope;
		}

		private function setScopeNone($obj, $scope) {
			// Nothing here;
		}

		private function setScopeVar($obj, $scope) {
			$obj->{$this->setScopeName} = $scope;
		}

		private function setScopeMethod($obj, $scope) {
			$obj->{$this->setScopeName}($scope);
		}

		private function setScopeThrow($obj, $scope) {
			throw $scope->Exception("setScope = '{$this->setScopeName}' is not valid");
		}

		public function newInstance($args, $scope) {
			$result =  call_user_func_array($this->constructor, $args);
			if(isset($this->info['complete'])) {
				$result->{$this->info['complte']}($scope);
			}
			return $result;
		}

	}


?>