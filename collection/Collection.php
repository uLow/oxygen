<?
namespace oxygen\collection;
    use ArrayAccess;
    use Countable;
    use IteratorAggregate;
    use oxygen\object\Object;

    abstract class Collection extends Object
        implements ArrayAccess, Countable, IteratorAggregate {
    }


?>