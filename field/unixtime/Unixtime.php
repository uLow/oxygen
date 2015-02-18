<?
namespace oxygen\field\unixtime;

    use oxygen\field\Field;

	class Unixtime extends Field {
    	const DATE_FORMAT = "d.m.Y H:i:s";

    	public function wrap($value){
    		return date(self::DATE_FORMAT, $value);
    	}

    	public function unwrap($value){
    		return strtotime($value);
    	}
    }


?>