<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Auth_Registry
{
    private $_authentications;

    private $_authorizations;

    private $_acl = null;

    public function __construct($authentications, $authorizations)
    {
        $this->_authentications = $authentications;
        $this->_authorizations  = $authorizations;
    }

    public function getAuthentications()
    {
        return $this->_authentications;
    }

    public function getAuthorizations()
    {
        return $this->_authorizations;
    }

    /**
     *
     * @return Zend_Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    public function setAcl($acl)
    {
        $this->_acl = $acl;
    }
}