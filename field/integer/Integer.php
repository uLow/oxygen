<?
namespace oxygen\field\integer;

    use oxygen\field\Field;

	class Integer extends Field {
    	public function wrap($value)
    	{
    		return (int)(string)$value;
    	}
    }


?>