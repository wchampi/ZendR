<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

if (!function_exists('curl_init')) {
  throw new Exception('Paypal needs the CURL PHP extension.');
}

class ZendR_Service_Paypal
{
    private $_apiUsername = null;
    private $_apiPassword = null;
    private $_apiSignature = null;
    private $_apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    private $_subject = null;
    private $_userProxy = false;
    private $_proxyHost = '127.0.0.1';
    private $_proxyPort = '808';
    private $_paypalUrl = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    private $_version = '64.0';
    private $_ackSuccess = 'SUCCESS';
    private $_ackSuccessWithWarning = 'SUCCESSWITHWARNING';

    public function  __construct($apiEndpoint = null)
    {
        if ($apiEndpoint != null) {
            $this->_apiEndpoint = $apiEndpoint;
        }
    }

    public function setApiUsername($apiUsername)
    {
        $this->_apiUsername = $apiUsername;
    }

    public function setApiPassword($apiPassword)
    {
        $this->_apiPassword = $apiPassword;
    }

    public function setApiSignature($apiSignature)
    {
        $this->_apiSignature = $apiSignature;
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }

    public function call($method, array $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);

        if ($this->_userProxy) {
            curl_setopt ($ch, CURLOPT_PROXY, $this->_proxyHost . ":" . $this->_proxyPort);
        }

        $urlParams = array (
            'VERSION' => $this->_version,
            'METHOD' => $method
        );
        if ($this->_apiUsername != null && trim($this->_apiUsername) != '') {
            $urlParams['USER'] = trim($this->_apiUsername);
        }
        if ($this->_apiPassword != null && trim($this->_apiPassword) != '') {
            $urlParams['PWD'] = trim($this->_apiPassword);
        }
        if ($this->_apiSignature != null && trim($this->_apiSignature) != '') {
            $urlParams['SIGNATURE'] = trim($this->_apiSignature);
        }
        if ($this->_subject != null && trim($this->_subject) != '') {
            $urlParams['SUBJECT'] = trim($this->_subject);
        }

        $urlParams = array_merge(
            $urlParams,
            $params
        );
        
        $urlArray = array();
        foreach ($urlParams as $key => $value) {
            $urlArray[] = $key . '=' . urlencode(trim($value));
        }
        $urlStr = implode('&', $urlArray);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $urlStr);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        } else {
            curl_close($ch);
        }

        return $this->_parseArray($response);
    }

    private function _parseArray($urlStr)
    {
        $intial = 0;
        $urlArray = array();

        while (strlen($urlStr)) {
            $keypos = strpos($urlStr, '=');

            $valuepos = strpos($urlStr, '&') ? strpos($urlStr, '&') : strlen($urlStr);

            $keyval = substr($urlStr, $intial, $keypos);
            $valval = substr($urlStr, $keypos + 1, $valuepos - $keypos - 1);

            $urlArray[urldecode($keyval)] = urldecode($valval);
            $urlStr = substr($urlStr, $valuepos + 1, strlen($urlStr));
        }
        
        return $urlArray;
    }
}
