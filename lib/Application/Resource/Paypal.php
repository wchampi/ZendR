<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

require_once 'ZendR/Service/Paypal.php';

class ZendR_Application_Resource_Paypal extends Zend_Application_Resource_ResourceAbstract
{
    /**
     *
     * @return  string
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        $options = $this->getOptions();
        if (!isset($options['username'])) {
            throw new Exception('Username Paypal not found');
        }

        if (!isset($options['password'])) {
            throw new Exception('Password Paypal not found');
        }

        if (!isset($options['signature'])) {
            throw new Exception('Signature Paypal not found');
        }

        if (!isset($options['endpoint'])) {
            throw new Exception('Endpoint Paypal not found');
        }

        $service = new ZendR_Service_Paypal($options['endpoint']);
        $service->setApiUsername($options['username']);
        $service->setApiPassword($options['password']);
        $service->setApiSignature($options['signature']);

        Zend_Registry::set('ServicePaypal', $service);

        return $service;
    }
}