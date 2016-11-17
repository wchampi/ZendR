<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Listener_Response implements ZendR_Doctrine_Import_Listener_Interface
{
    /**
     * @var Zend_Tool_Framework_Client_Response
     */
    private $_response = null;

    private $_builtCount = 0;

    public function __construct(Zend_Tool_Framework_Client_Response $response)
    {
        $this->_response = $response;
    }

    /**
     * @param string $className
     * @param string $moduleName
     */
    public function notifyRecordBuilt($className, $moduleName)
    {
        if ($moduleName == null) {
            $this->_response->appendContent("[Doctrine] Generated record '$className'.", array('color' => 'green'));
        } else {
            $this->_response->appendContent("[Doctrine] Generated record '$className' in Module '$moduleName'.", array('color' => 'green'));
        }
        $this->_builtCount++;
    }

    public function notifyImportCompleted()
    {
        $this->_response->appendContent(
            '[Doctrine] Successfully generated '.$this->_builtCount.' record classes.', array('color' => 'green')
        );
    }
}