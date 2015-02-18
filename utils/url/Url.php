<?
namespace oxygen\utils\url;
    //TODO: Finish the functionality

    class URL {
        public static function pathFor($url) {
        }
        public static function urlFor($url) {
        }
        public static function Absolutize($base, $relative = false) {
            if($relative !== false) {
                return url_to_absolute($base, $relative);
            } else if(preg_match('|^(https?://[^/]*)(/)(.*)$|',$base,$m)) {
                return $base;
            } else {
                throw new \oxygen\exception\Exception('Not impelemented yet');
            }
        }
    }

?>