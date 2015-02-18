<?
namespace oxygen;
    use oxygen\controller\Controller;
    use oxygen\scope\Scope;

    class Oxygen {


        public static function __class_construct($scope) {
            /* Intentionally left blank. No code here */
        }

        private static $stack = array();
        private static $sp = 0;

        public static function push($call){
            self::$stack[self::$sp++] = $call;
        }

        public static function pop() {
            return self::$stack[--self::$sp];
        }

        public static function peek() {
            if (self::$sp > 0) {
                return self::$stack[self::$sp-1];
            } else {
                return null;
            }
        }

        public static function open($tag = 'div', $data = array()){
            if(is_array($tag)) {
                $data = $tag;
                $tag = 'div';
            }
            preg_match_all('/(([A-Za-z_]+)="([^"]+)")/', $tag, $attrs);
            preg_match_all('/#([A-Za-z_0-9\-]+)/', $tag, $ids);
            preg_match_all('/\.([A-Za-z_0-9\-]+)/', $tag, $classes);
            $ids = $ids[1];
            $classes = $classes[1];
            preg_match('/^[A-Za-z:_0-9]+/', $tag, $tagm);
            $tag  = $tagm[0];
            $attrs = $attrs[1];
            $call = self::$stack[self::$sp-1];
            $sp = self::$sp-1;
            if(!method_exists($call->instance, 'go')){
                while($sp >= 0 && !self::$stack[$sp]->instance instanceof Controller) {
                    $sp--;
                }
                if ($sp < 0) throw new \Exception("No controller available");
            }
            $remote = self::$stack[$sp]->instance->go();
            if($remote != '/')$remote = $remote.'/';
            $data['remote'] = $remote;
            $data['component'] = $call->name;
            $call->stack[$call->sp++] = $tag;
            $id = '';
            foreach($ids as $_id) {
               $id = ' id="'. $_id .'"';
            }

            echo '<' . $tag . $id . ' class="' . self::getCssClass();
            foreach($classes as $class) {
                echo ' '. $class;
            }
            echo '"';
            foreach ($attrs as $a) {
                echo ' '.$a;
            }
            if(is_array($data)) {
                foreach ($data as $key => $value) {
                    if(!is_string($value)){
                        $value = json_encode($value);
                    }else{
						$value_ = json_decode($value);
						if($value_!==null){
                            /*if(!preg_match('/^\//',$value)){
                                $value = json_encode($value);                            
                            }*/
						}else{
							if(preg_match('/^[0]+/',$value)){
								$value = json_encode($value);
							}
						}
					}
                    echo ' data-' . $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '"';
                }
            }
            echo '>';
        }

        public static function cssClassFor($class, $name) {
            return 'css-' . implode('_', explode('\\', $class)) . '-' . $name;
        }

        public static function close() {
            $call = self::$stack[self::$sp-1];
            $tag = $call->stack[--$call->sp];
            echo '</' . $tag . '>';
        }

        public static function closeAll() {
            $call = self::$stack[self::$sp-1];
            while ($call->sp > 0) {
                $tag = $call->stack[--$call->sp];
                echo '</' . $tag . '>';
            }
        }

        public static function getCssClass() {
            if(self::$sp === 0) throw new \Exception(
                'getCssClass() call is valid only within template code'
            );
            $call = self::$stack[self::$sp-1];
            if ($call->component === false) {
                return $call->component = self::cssClassFor(
                    $call->class,
                    $call->name
                );
            } else {
                return $call->component;
            }
        }

        public static function Generate() {
            $scope = Scope::getRoot();
            $generator = $scope->Generator();
            $generator->generate();
        }
    }


?>