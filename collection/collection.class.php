<?
namespace oxygen\collection;
    use ArrayAccess;
    use Countable;
    use IteratorAggregate;
    use oxygen\object\Oxygen_Object;

    abstract class Oxygen_Collection extends Oxygen_Object
        implements ArrayAccess, Countable, IteratorAggregate {
    }


?>