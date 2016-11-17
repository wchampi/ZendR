<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Controller_Plugin_Popup extends Zend_Controller_plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $params = $this->getRequest()->getParams();
        $layout = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getResource('layout');
        $layout->getView()->popup = false;
        if (isset ($params['popup'])) {
            $layout->getView()->popup = true;
        }
    }

	public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $parmas = $this->getRequest()->getParams();
        $layout = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getResource('layout');
        if (isset ($parmas['popup'])) {
            $layout->disableLayout();
        }
    }
}