<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Application_Resource_Apps extends Zend_Application_Resource_ResourceAbstract
{
    private $_config;

     /**
     *
     * @return ZendR_Apps_Config
     */
    private function _initConfig()
    {
        $options = $this->getOptions();
        $this->_config = new ZendR_Apps_Config(
            ZendR_Sf_Yml::load($options['configFile'])
        );
    }

    private function _obtenerTranslate($params)
    {
        if (!isset($params['application']['property']['multilanguage']['adapter'])) {
            throw new Exception('have not entered the adapter type, para multilanguage de '
                . $params['application']['name']);
        }

        if (!isset($params['application']['property']['multilanguage']['data'])) {
            throw new Exception('have not entered the data path, para multilanguage'
                . $params['application']['name']);
        }

        return new Zend_Translate(
            trim($params['application']['property']['multilanguage']['adapter']),
            ZendR_Util::obtenerPathIniString($params['application']['property']['multilanguage']['data']),
            null,
            array('scan' => Zend_Translate::LOCALE_FILENAME)
        );
    }

    private function _initMultiLanguage()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('FrontController');
        try {
            $bootstrap->bootstrap('Layout');
            $bootstrap->bootstrap('Locale');
            $bootstrap->bootstrap('Router');
        } catch (Exception $e) {
            
        }

        $front = $bootstrap->getResource('FrontController');

        $request = new Zend_Controller_Request_Http();
        $front->getRouter()->route($request);

        $baseUrl    = $request->getBaseUrl();
        $requestUri = $request->getRequestUri();

        $paramsUrl = empty($baseUrl) ? substr($requestUri, 1) : str_replace("$baseUrl/", '', $requestUri);

        $cookieLang = false;
        $paramsController = $this->_config->obtenerParams($request->getModuleName(), $request->getControllerName());
        if ($paramsController) {
            if (isset($paramsController['application']['property']['multilanguage'])) {
                if (isset($paramsController['application']['property']['multilanguage']['cookie'])) {
                    if ($paramsController['application']['property']['multilanguage']['cookie'] == 'true') {
                        $cookieLang = true;
                    }
                }

                $translate = $this->_obtenerTranslate($paramsController);
                if (!is_array($translate->getAdapter()->getList())) {
                    throw new Exception('There is no translation from one language');
                }
                
                try {
                    if ($cookieLang && isset($_COOKIE['requestLanguage'])) {
                        $requestLanguage = $_COOKIE['requestLanguage'];
                    } else {
                        $locale = new Zend_Locale();
                        $locale->setLocale(Zend_Locale::BROWSER);
                        $requestLanguage = $locale->getLanguage();
                    }
                    
                    if (!in_array($requestLanguage, $translate->getAdapter()->getList())) {
                        throw new Exception('Found no valid language: ' . $requestLanguage);
                    }
                } catch (Exception $e) {
                    $locale = Zend_Registry::isRegistered('Zend_Locale') ? Zend_Registry::get('Zend_Locale') : new Zend_Locale();
                    $requestLanguage = $locale->getLanguage();
                }

                if (in_array($requestLanguage, $translate->getAdapter()->getList())) {
                    if (!$cookieLang) {
                        $this->_config->setRedirectUrl($baseUrl . '/' . $requestLanguage . '/' . $paramsUrl);
                        return;
                    }
                } else {
                    throw new Exception('Found no valid language');
                }

            }
        }

        $paramsArray = explode('/', $paramsUrl);
        if (!$cookieLang) {
            $requestLanguage = $paramsArray[0];

            $request = new Zend_Controller_Request_Http();
            $request->setBaseUrl($baseUrl . '/' . $requestLanguage);
            
            $front->getRouter()->route($request);

            $paramsController = $this->_config->obtenerParams($request->getModuleName(), $request->getControllerName());
        } else {
            $front->getRouter()->route($request);
        }
        
        if ($paramsController) {
            if (isset($paramsController['application']['property']['multilanguage'])) {
                $multilanguage = $paramsController['application']['property']['multilanguage'];
                $translate = $this->_obtenerTranslate($paramsController);
                
                if (isset($multilanguage['cookie'])) {
                    if ($multilanguage['cookie'] == 'true') {
                        if (in_array($paramsArray[0], $translate->getAdapter()->getList())) {
                            unset($paramsArray[0]);
                            $this->_config->setRedirectUrl($baseUrl . '/' . implode('/', $paramsArray));
                            return;
                        }
                        $cookieLang = true;
                    }
                }    
                
                if (!is_array($translate->getAdapter()->getList())) {
                    throw new Exception('There is no translation from one language');
                }

                if (in_array($requestLanguage, $translate->getAdapter()->getList())) {
                    $layout = $bootstrap->getResource('Layout');
                    if (!$layout) {
                        throw new Exception('Layout has not been enabled.');
                    }

                    // LOCALE
                    $locale = Zend_Registry::isRegistered('Zend_locale') ? Zend_Registry::get('Zend_Locale') : new Zend_Locale();
                    $locale->setLocale($requestLanguage);
                    Zend_Registry::set('Zend_Locale', $locale);

                    // TRANSLATE
                    $translate->setLocale($locale);
                    Zend_Registry::set('Zend_Translate', $translate);
                    
                    if (!$cookieLang) {
                        unset($paramsArray[0]);
                    }    
                    
                    $this->_config->setBaseUrlOriginal($baseUrl);
                    $this->_config->setParamsUrl(implode('/', $paramsArray));
                    
                    if (!$cookieLang) {
                        $front->setBaseUrl($baseUrl . '/' . $requestLanguage);
                    }    
                    
                    // DATE FORMAT
                    if (isset($multilanguage['date_format'])) {
                        if (is_array($multilanguage['date_format'])) {
                            foreach ($multilanguage['date_format'] as $language => $dateFormat) {
                                if ($language == $requestLanguage) {
                                    $this->_setDateFormat($dateFormat);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function _initDateFormat()
    {
        $options = $this->getOptions();
        if (isset($options['date'])) {
            if (isset($options['date']['date_format'])) {
                $this->_setDateFormat($options['date']['date_format']);
            }
        }
    }

    private function _setDateFormat($dateFormat)
    {
        Zend_Registry::set('date_format', $dateFormat);
        Zend_Registry::set('dateFormatJq', str_replace(
            array('d','m','y'),
            array('dd','mm','yy'),
            strtolower($dateFormat)
        ));
    }

    private function _initUploadPath()
    {
        $uploadPath = APPLICATION_PATH . "/../public/uploads";
        $options = $this->getOptions();
        if (isset($options['upload_path'])) {
            if (!defined('UPLOAD_PATH')) {
                $uploadPath = $options['upload_path'];
                if (is_dir($uploadPath)) {
                    define('UPLOAD_PATH', realpath($uploadPath));
                } else {
                    throw new Exception("UPLOAD_PATH '" . $uploadPath . "' not found");
                }
            }
        } elseif (!is_dir(APPLICATION_PATH . "/../public")) {
            throw new Exception("resources.apps.upload_path not defined");
        }

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath);
        }

        if (!defined('UPLOAD_PATH')) {
            define('UPLOAD_PATH', realpath($uploadPath));
        }
    }

    private function _initTranslate()
    {
        try {
            $bootstrap = $this->getBootstrap();
            $bootstrap->bootstrap('Translate');
        } catch (Exception $e) {

        }

        if (!Zend_Registry::isRegistered('Zend_Translate')) {
            $translate = new Zend_Translate(
                array(
                    'adapter' => 'array',
                    'content' => array('ss'=>'ss'),
                    'locale'  => 'en'
                )
            );
            Zend_Registry::set('Zend_Translate', $translate);
        }
    }
    
    /**
     *
     * @return  string
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('FrontController');
        $front = $bootstrap->getResource('FrontController');
        $front->registerPlugin(new ZendR_Controller_Plugin_Apps());

        $options = $this->getOptions();
        if (isset($options['configFile'])) {
            $this->_initConfig();
            $this->_initMultiLanguage();
        }

        if (isset($options['date'])) {
            if (isset($options['date']['timezone'])) {
                @date_default_timezone_set($options['date']['timezone']);
            }
        }

        if (!Zend_Registry::isRegistered('date_format')) {
            $this->_initDateFormat();
        }
        
        if (Zend_Registry::isRegistered('date_format')) {
            Zend_Date::setOptions(array('format_type' => 'php'));
        }

        $this->_initTranslate();
        $this->_initUploadPath();

        return $this->_config;
    }
}