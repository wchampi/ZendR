<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

require_once 'ZendR/Facebook.php';

class ZendR_Application_Resource_Facebook extends Zend_Application_Resource_ResourceAbstract
{
    /**
     *
     * @return  string
     * @throws  Zend_Application_Resource_Exception
     */
    public function init()
    {
        $options = $this->getOptions();
        if (!isset($options['appId'])) {
            throw new Exception('AppId Facebook not found');
        }
        $optionFacebook['appId'] = $options['appId'];

        if (isset($options['secret'])) {
            $optionFacebook['secret'] = $options['secret'];
        }
        
        $cookie = false;
        if (isset($options['cookie'])) {
            if ($options['cookie']) {
                $cookie = true;
            }
        }
        $optionFacebook['cookie'] = $cookie;

        $ssl = true;
        if (isset($options['ssl'])) {
            if (!$options['ssl']) {
                $ssl = false;
            }
        }

        if (!$ssl) {
            ZendR_Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
            ZendR_Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;
        }

        $facebook = new ZendR_Facebook($optionFacebook);
        Zend_Registry::set('Facebook', $facebook);

        return $facebook;
    }
}