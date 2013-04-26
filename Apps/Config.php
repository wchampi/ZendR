<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Apps_Config
{
    private $_config = array();

    private $_redirectUrl = null;

    private $_dateFormatJq = null;

    private $_baseUrlOriginal = null;

    private $_paramsUrl = null;

    /**
     *
     * @param array $config
     */
    public function  __construct($config)
    {
        if (is_array($config)) {
            $this->_config = $config;
        } else {
            throw new Exception('Verified configFile');
        }
        
    }

    public function obtenerParams($moduleName, $controllerName)
    {
        foreach ($this->_config as $applicationName => $propertyApplication) {
            if (!isset($propertyApplication['layouts'])) {
                break;
            }
            if (!is_array($propertyApplication['layouts'])) {
                break;
            }

            if (isset($propertyApplication['e-commerce'])) {
                if ($propertyApplication['e-commerce'] == 'true') {
                    $eCommerce = true;
                }
            }

            foreach ($propertyApplication['layouts'] as $layout => $propertyLayout) {
                if (!isset($propertyLayout['modules'])) {
                    break;
                }
                if (!is_array($propertyLayout['modules'])) {
                    break;
                }

                foreach ($propertyLayout['modules'] as $module => $propertyModule) {
                    if (!is_array($propertyModule['controllers'])) {
                        break;
                    }

                    if ($moduleName == $module && array_key_exists($controllerName, $propertyModule['controllers'])) {
                        $params['application']['name'] = $applicationName;
                        $params['application']['property'] = $propertyApplication;
                        $params['layout']['name'] = $layout;
                        $params['layout']['property'] = $propertyLayout;
                        $params['module']['name'] = $moduleName;
                        $params['module']['property'] = $propertyModule;
                        $params['controller']['name'] = $controllerName;
                        $params['controller']['property'] = $propertyModule['controllers'][$controllerName];

                        return $params;
                    }
                }
            }
        }
        return false;
    }

    public function setRedirectUrl($redirectUrl)
    {
        $this->_redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    public function setDateFormatJq($dateFormatJq)
    {
        $this->_dateFormatJq = $dateFormatJq;
    }

    public function getDateFormatJq()
    {
        return $this->_dateFormatJq;
    }

    public function setBaseUrlOriginal($baseUrlOriginal)
    {
        $this->_baseUrlOriginal = $baseUrlOriginal;
    }

    public function getBaseUrlOriginal()
    {
        return $this->_baseUrlOriginal;
    }

    public function setParamsUrl($paramsUrl)
    {
        $this->_paramsUrl = $paramsUrl;
    }

    public function getParamsUrl()
    {
        return $this->_paramsUrl;
    }
}