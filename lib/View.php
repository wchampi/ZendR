<?php

class ZendR_View extends Zend_View
{
    public static function fullUrl($url)
    {
        $view = new Zend_View();
        return $view->serverUrl($view->baseUrl($url));
    }
}