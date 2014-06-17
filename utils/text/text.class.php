<?

    class Oxygen_Utils_Text {
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

    }

?>