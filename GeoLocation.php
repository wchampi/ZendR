<?php

include_once dirname(__FILE__) . '/GeoLocation/geoipcity.inc';

class ZendR_GeoLocation 
{
    
    public function __construct()
    {
        
    }
    
    public function fetchCountryCode($ip)
    {
        if (trim($ip) == '') {
            return '';
        }
        
        $gi = geoip_open(dirname(__FILE__) . '/GeoLocation/GeoLiteCity.dat', GEOIP_STANDARD);
        $record = geoip_record_by_addr($gi, $ip);
        geoip_close($gi);
        
        if ((isset($record)) and (strlen($record->country_code) == 2)) {
            return $record->country_code;
        }
        
        return '';
    }
}