<?
namespace oxygen\field\integer;

    use oxygen\field\Oxygen_Field;

	class Oxygen_Field_Integer extends Oxygen_Field {
    	public function wrap($value)
    	{
    		return (int)(string)$value;
    	}
    }


?>