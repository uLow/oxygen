<?
namespace oxygen\utils\text;
    class Text {
        public static function __class_construct($scope) {
            /* Intentionally left blank. No code here */
        }

        private static $diacritics = array(
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'I',  'Ķ' => 'K',
            'Ļ' => 'L', 'Ņ' => 'N', 'Š' => 'S', 'Ū' => 'U', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i',  'ķ' => 'k',
            'ļ' => 'l', 'ņ' => 'n', 'š' => 's', 'ū' => 'u', 'ž' => 'z',  'ç' => 'e',
            'í' => 'k', 'â' => 'a'
        );
        const REGEXP_DIACRITICS = '/(Ā|Č|Ē|Ģ|Ī|Ķ|Ļ|Ņ|Š|Ū|Ž|ā|č|ē|ģ|ī|ķ|ļ|ņ|š|ū|ž|ç|í|â)/';

        public static function removeDiacritics($text) {
            return preg_replace_callback(
                self::REGEXP_DIACRITICS,
                function($m){
                    return self::$diacritics[$m[1]];
                },
                //'self::$diacritics["\\1"]',
                $text
            );
        }
        public static function format($format) {
            $args = func_get_args();
            //return preg_replace('/{([0-5])}/e','$arg\\1',$format);
            return preg_replace_callback(
                '/{([0-5])}/',
                function($m) use($args){
                    return $args[$m[1]+1];
                },
                $format
            );
        }

        public static function humanize($name) {
            $x = str_replace('_', ' ', $name);
            $x = preg_replace('/\s(en|ru|lv)$/','(\\1)',$x);
            $x = preg_replace('/id$/','ID',$x);
            return ucfirst($x);
        }

        static public function ns($name){
            $parts = explode('_', $name);
            $last = $parts[count($parts)-1];
		    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', implode('\\', $parts))) . '\\' . $last;
        }

        static public function snakeify($input){
            preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
            $ret = $matches[0];
            foreach ($ret as &$match) {
                $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
            }
            return implode('_', $ret);
        }

        static public function classToNamespace($namespace, $className, $snakeify = true){
            if($snakeify === true){
                $className = self::snakeify($className);
            }
            return $namespace.'\\'.$className;
        }

    }

?>