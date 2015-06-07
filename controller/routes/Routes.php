<?
namespace oxygen\controller\routes;

use ArrayAccess;
use oxygen\object\Object;

/**
 * show off @method
 * @method \oxygen\sql\database\Database Database()
 * @method \oxygen\controller\configurator\Configurator Configurator()
 */
class Routes extends Object implements ArrayAccess
{
    public $controller = null;

    /**
     * @param \oxygen\controller\Controller $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function offsetExists($offset)
    {
        $this->throw_Exception('Not implemented yet');
    }

    public function offsetSet($offset, $value)
    {
        $this->controller->addExplicit($offset, $value);
    }

    public function offsetGet($offset)
    {
        return $this->scope->Configurator($this->controller, $offset);
    }

    public function offsetUnset($offset)
    {
        $this->throw_Exception('Not implemented yet');
    }
}


?>