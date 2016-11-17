<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{
    protected $_dbTable = null;

    protected $_dbTableCredential = null;

    protected $_fieldRelated = null;

    protected $_identityColumn = null;

    protected $_credentialColumn = null;

    protected $_identity = null;

    protected $_credential = null;

    protected $_resultRow = null;

    public function __construct($table, $tableCredential = null, $fieldRelated = null)
    {
        $this->_dbTable = Doctrine::getTable($table);
        if ($tableCredential != null && $fieldRelated != null) {
            $this->_dbTableCredential   = Doctrine::getTable($tableCredential);
            $this->_fieldRelated        = $fieldRelated;
        }
    }

    public function setIdentityColumn($identityColumn)
    {
        $this->_identityColumn = $identityColumn;
        return $this;
    }

    public function setCredentialColumn($credentialColumn)
    {
        $this->_credentialColumn = $credentialColumn;
        return $this;
    }

    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    public function getResultRowObject()
    {
        return $this->_resultRow;
    }

    public function authenticate()
    {
        $this->_authenticateSetup();
        $resultIdentities = $this->_getIdentities();

        if ( ($authResult = $this->_authenticateValidateResultset($resultIdentities)) instanceof Zend_Auth_Result) {
            return $authResult;
        }

        $authResult = $this->_authenticateValidateResult($resultIdentities[0]);
        return $authResult;
    }

    protected function _authenticateSetup()
    {
        $exception = null;

        if ($this->_identityColumn == '') {
            $exception = 'An identity column must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_credentialColumn == '') {
            $exception = 'A credential column must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Zend_Auth_Adapter_DbTable.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Zend_Auth_Adapter_DbTable.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }

        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );

        return true;
    }

    protected function _getIdentities()
    {
        $identities = $this->_dbTable->findBy($this->_identityColumn, $this->_identity);

        if ($this->_dbTableCredential) {
            $arrIdentities = array();
            $fieldRelated = $this->_fieldRelated;
            foreach ($identities as $identity) {
                $resultCredential = $this->_dbTableCredential->findOneBy($fieldRelated, $identity->$fieldRelated);
                if ($resultCredential) {
                    $arrIdentities[] = $identity;
                }
            }

            return $arrIdentities;
        }
        return $identities;
    }

    protected function _authenticateValidateResultSet($resultIdentities)
    {
        if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }

        return true;
    }

    protected function _authenticateValidateResult($resultIdentity)
    {
        $credentialColumn = $this->_credentialColumn;

        if ($this->_dbTableCredential) {
            $fieldRelated = $this->_fieldRelated;
            $resultCredentials = $this->_dbTableCredential->findBy($fieldRelated, $resultIdentity->$fieldRelated);

            if ( ($authResult = $this->_authenticateValidateResultset($resultCredentials)) instanceof Zend_Auth_Result) {
                return $authResult;
            }

            $resultCredential = $resultCredentials[0];
            if ($resultCredential->$credentialColumn != $this->_credential) {
                $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
                return $this->_authenticateCreateAuthResult();
            }

            $this->_resultRow = $resultCredential;
        } else {
            if ($resultIdentity->$credentialColumn != $this->_credential) {
                $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
                return $this->_authenticateCreateAuthResult();
            }

            $this->_resultRow = $resultIdentity;
        }

        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }

    protected function _authenticateCreateAuthResult()
    {
        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
            );
    }
}