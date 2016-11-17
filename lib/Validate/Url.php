<?php

class ZendR_Validate_Url extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL   => "'%value%' is not a valid URL.",
    );

    public function isValid($value, $http = false)
    {
        $value = str_replace(array(' ', 'ñ', 'Ñ'), array('', '', ''), ZendR_String::parseString($value)->toUTF8()->__toString());
        if ($http) {
            if (Zend_Uri::check($value)) {
                return true;
            }
        } else {
            if (Zend_Uri::check($value) || Zend_Uri::check('http://' . $value)) {
                return true;
            }
        }
        return false;
    }
}
