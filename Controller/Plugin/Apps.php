<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Controller_Plugin_Apps extends Zend_Controller_plugin_Abstract
{
    /**
     *
     * @var ZendR_Apps_Config
     */
    private $_apps;

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $resourceLayout = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('layout');
        if (!$resourceLayout) {
            throw new Exception('Layout is not defined');
        }
        $view = $resourceLayout->getView();
        
        if (Zend_Registry::isRegistered('dateFormatJq')) {
            $view->dateFormatJq = Zend_Registry::get('dateFormatJq');
        }

        if (!Zend_Registry::isRegistered('config')) {
            Zend_Registry::set(
                'config',
                new Zend_Config(Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions())
            );
        }
        
        $this->_apps = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('apps');
        if ($this->_apps === null) {
            $this->_apps = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getResource('ZendR_Application_Resource_Apps');
        }   

        if ($this->_apps !== null) {
            if ($this->_apps->getRedirectUrl()) {
                header('Location: ' . $this->_apps->getRedirectUrl());
                exit;
            }

            $params = $this->_apps->obtenerParams(
                $this->getRequest()->getModuleName(),
                $this->getRequest()->getControllerName()
            );

            if ($params) {
                $basePath = Zend_Controller_Front::getInstance()
                    ->getParam('bootstrap')
                    ->getResourceLoader()
                    ->getBasePath();
                $sesionName = $basePath . DIRECTORY_SEPARATOR . $params['application']['name'];

                $session = new Zend_Session_Namespace($sesionName);
                Zend_Registry::set('session', $session);
                
                $resourceLayout->setLayout($params['layout']['name']);

                $view->paramsUrl        = $this->_apps->getParamsUrl();
                $view->baseUrlOriginal  = $this->_apps->getBaseUrlOriginal();

                $view->registerHelper(new ZendR_View_Helper_HtmlYoutube(), 'htmlYoutube');
                $view->registerHelper(new ZendR_View_Helper_Thumb(), 'thumb');
                $view->registerHelper(new ZendR_View_Helper_BaseUrl(), 'baseUrl');

                // AUTHENTICACION SIMPLE
                if (isset($params['application']['property']['authentication'])) {
                    $propertyAuth = $params['application']['property']['authentication'];
                    if (isset($propertyAuth['type'])) {
                        if ($propertyAuth['type'] == 'simple' || $propertyAuth['type'] == 'acl') {
                            if (!isset($propertyAuth['url'])) {
                                throw new Exception('has not been entered auth url.');
                            }
                            $this->_auth($params, $session, $view);
                        }
                    }
                }

                if (isset($params['application']['property']['e-commerce'])) {
                    if ($params['application']['property']['e-commerce'] == '1') {
                         $view->cart = ZendR_Store_Cart_Factory::createInstance($session);
                    }
                }

                if (isset($params['application']['property']['title'])) {
                     $view->headTitle($params['application']['property']['title'], true);
                }
            }
        }

        $bootstrap = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap');
        $methodInit = 'initAppsPreDispatch';

        if (method_exists($bootstrap, $methodInit)) {
            $bootstrap->$methodInit();
        }
    }

    public function _auth($params, $session, $view)
    {
        $propertyApplication = $params['application']['property'];
        $propertyLayout = $params['layout']['property'];
        $propertyModule = $params['module']['property'];

        $auth = false;
        if (isset($propertyLayout['auth'])) {
            if ($propertyLayout['auth'] == 'true') {
                $auth = true;
            }
        }

        if (isset($propertyModule['auth'])) {
            if ($propertyModule['auth'] == 'true') {
                $auth = true;
            } elseif ($propertyModule['auth'] == 'false') {
                $auth = false;
            }
        }

        $controller = $this->getRequest()->getControllerName();
        if (is_array($propertyModule['controllers'][$controller])) {
            foreach ($propertyModule['controllers'][$controller] as $property) {
                if ($property == 'noauth') {
                    $auth = false;
                    break;
                } elseif ($property == 'auth') {
                    $auth = true;
                    break;
                }
            }
        }

        if ($auth) {
            if (!$session->userLogin) {
                header('Location: ' . $view->baseUrl($propertyApplication['authentication']['url']));
                exit;
            } else if ($params['application']['property']['authentication']['type'] == 'acl' 
                && isset($params['application']['property']['authentication']['resources'])
                && isset($session->userLogin->accesos) && isset($session->userLogin->root)) {
                
                $acl = new Zend_Acl();
                $acl->addRole(new Zend_Acl_Role('userLogin'));

                if (is_array($params['application']['property']['authentication']['resources'])) {
                    foreach ($params['application']['property']['authentication']['resources'] as $resource) {
                        if (!$acl->has($resource)) {
                            $acl->add(new Zend_Acl_Resource($resource));
                        }
                        //if (!$acl->has($acceso)) {
                          //  $acl->add(new Zend_Acl_Resource($acceso));
                        //}
                    }
                }
                
                if ($session->userLogin->root) {
                    $acl->allow('userLogin');
                } else {
                    $accesos = json_decode($session->userLogin->accesos);
                    if (is_array($accesos)) {
                        foreach ($accesos as $acceso) {
                            if ($acl->has($acceso)) {
                                $acl->allow('userLogin', $acceso);
                            }
                            
                            $resourceAccion = explode('|', $acceso);
                            if (isset($resourceAccion[0])) {
                                if ($acl->has($resourceAccion[0])) {
                                    $acl->allow('userLogin', $resourceAccion[0]);
                                }
                            }
                        }
                    }

                    $resource = $this->getRequest()->getModuleName() . ':'
                        . $this->getRequest()->getControllerName() . ':'
                        . $this->getRequest()->getActionName();
                    if ($acl->has($resource)) {
                        if (!$acl->isAllowed('userLogin', $resource)) {
                            echo '<h1>Acceso Denegado</h1>';
                            exit;
                        }
                    }
                }

                $view->acl = $acl;
                Zend_Registry::set('acl', $acl);
            }
        }
    }
}