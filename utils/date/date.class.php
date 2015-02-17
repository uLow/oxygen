<?
namespace oxygen\utils\date;
    use Exception;

    class Oxygen_Utils_Date {
        public static function Convert($formatFrom,$formatTo,$date) {
            if($formatFrom === 'm.d.Y') {
                return date($formatTo,strtotime($date));
            } else {
                throw new Exception("Date fromat $fromatFrom is not implemented yet!");
            }
        }

        public static function getDaysKey($daysCount){
        	if($daysCount % 10 == 1){
        		return 'days1';
        	}else if($daysCount % 10 > 1 && $daysCount % 10 < 5){
        		return 'days2';
        	}else{
        		return 'days3';
        	}
        }
    }



?>