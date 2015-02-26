<?
namespace oxygen\factory;

use oxygen\object\Object;

abstract class Factory extends Object
{
    private $definition = null;

    /**
     * @param $definition
     */
    public final function __construct($definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param array $args
     * @param null $scope
     * @return mixed
     */
    public abstract function getInstance($args = array(), $scope = null);

    /**
     * @return null
     */
    public final function getDefinition()
    {
        return $this->definition;
    }

    /**
     *
     */
    public function __wakeup()
    {
        $this->reflected = false;
    }
}


?>