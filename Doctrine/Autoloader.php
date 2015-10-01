<?php

class ZendR_Doctrine_Autoloader implements Zend_Loader_Autoloader_Interface
{
    protected $_loadedModels;

    public function __construct($loadedModels)
    {
        $this->_loadedModels = $loadedModels;
    }

    public function autoload($class)
    {
        if (!isset($this->_loadedModels->$class)) {
            return false;
        }

        if (!class_exists($class)) {
            include_once $this->_loadedModels->$class;
        }
        return true;
    }
}
