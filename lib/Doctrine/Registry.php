<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Registry
{
    /**
     * @var Doctrine_Manager
     */
    protected $_manager = null;
    protected $_connections = array();
    protected $_generateModelOptions = array();
    protected $_paths = array();

    /**
     * @param Doctrine_Manager $manager
     * @param array $connections
     * @param array $paths
     * @param array $generateModelOptions
     */
    public function __construct(Doctrine_Manager $manager, array $connections, array $paths, array $generateModelOptions)
    {
        $this->_manager = $manager;
        $this->_connections = $connections;
        $this->_generateModelOptions = $generateModelOptions;
        $this->_paths = $paths;
    }

    /**
     * @return Doctrine_Manager
     */
    public function getManager()
    {
        return $this->_manager;
    }

    public function getConnections()
    {
        return $this->_connections;
    }

    public function getGenerateModelOptions()
    {
        return $this->_generateModelOptions;
    }

    public function getModelPath()
    {
        if (isset($this->_paths['model_path'])) {
            return $this->_paths['model_path'];
        }
        return null;
    }
}