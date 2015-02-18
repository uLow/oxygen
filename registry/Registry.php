<?php
namespace oxygen\registry;

use oxygen\object\Object;

class Registry extends Object {
    static public $implementations = array(
        'Router'             => 'oxygen\\router\\Router',
        'Routes'             => 'oxygen\\controller\\routes\\Routes',
        'Configurator'       => 'oxygen\\controller\\configurator\\Configurator',
        'Controller'         => 'oxygen\\controller\\Controller',
        'ControllerSection'  => 'oxygen\\controller\\section\\Section',
        'Dummy'              => 'oxygen\\controller\\dummy\\Dummy',
        'ChildrenIterator'   => 'oxygen\\controller\\iterator\\Iterator'
    );

    static public function register($name, $fullName){
        if(!isset(self::$implementations[$name])){
            self::$implementations[$name] = $fullName;
        }else{
            throw new \Exception('Redefined implementation of '.$name.'. Already defined as '.self::$implementations[$name].'.');
        }
    }

    static public function getFullName($name){
        if(isset(self::$implementations[$name])){
            return self::$implementations[$name];
        }else{
            throw new \Exception('Undefined class '.$name);
        }
    }

    static public function isRegistred($name){
        return isset(self::$implementations[$name]);
    }
}