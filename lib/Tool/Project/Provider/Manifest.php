<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

require_once 'ZendR/Tool/Project/Provider/ZendR.php';

class ZendR_Tool_Project_Provider_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable
{

    /**
     * getProviders()
     *
     * @return array Array of Providers
     */
    public function getProviders()
    {

        return array(
			'ZendR_Tool_Project_Provider_ZendR',
        );
    }
}