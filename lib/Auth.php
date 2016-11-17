<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Auth extends Zend_Auth
{
    /**
     *
     * @param string $namespace
     * @return Zend_Auth
     */
    public static function getInstance($namespace = 'default')
    {
        parent::getInstance()->setStorage(new Zend_Auth_Storage_Session($namespace, 'storage'));
        return parent::getInstance();
    }

    public static function findIdentity($namespace = 'default')
    {
        $auth = self::getInstance($namespace);
        if ($auth->hasIdentity()) {
            return $auth->getIdentity();
        }

        return null;
    }

    public static function logout($namespace = 'default')
    {
        self::getInstance($namespace)->clearIdentity();
    }

    public static function isLoggedIn($namespace = 'default')
    {
        return self::getInstance($namespace)->hasIdentity();
    }

    public static function login($nick, $password, $namespace = 'default')
    {
        if (!empty($nick) && !empty($password)) {
            $result = self::getInstance($namespace)->authenticate(self::getAdapter($nick, $password, $namespace));
            switch ($result->getCode())
            {
                case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                    throw new Exception($this->_messages[self::NOT_IDENTITY]);
                    break;
                case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                    throw new Exception($this->_messages[self::INVALID_CREDENTIAL]);
                    break;

                case Zend_Auth_Result::SUCCESS:
                    if ($result->isValid()) {
                        Zend_Auth::getInstance()->getStorage()->write($autAdapter->getResultRowObject());
                    } else {
                        throw new Exception($this->_messages[self::INVALID_USER]);
                    }
                    break;

                default:
                    throw new Exception($this->_messages[self::INVALID_LOGIN]);
                    break;
            }

        } else {
            throw new Exception($this->_messages[self::INVALID_LOGIN]);
        }
        return $this;
    }

    public static function getAdapter($nick, $password, $namespace = 'default')
    {
        $auth = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('auth');
        

        $adapter = new ZendR_Auth_Adapter_Doctrine('Cliente');
        $adapter->setIdentityColumn('des_email');
        $adapter->setCredentialColumn('des_password');
        $adapter->setIdentity($nick);
        $adapter->setCredential(md5($password));

        return $adapter;
    }
}