<?php

class ZendR_Sftp
{

    private $_server = 'localhost';
    private $_port = '22';
    private $_user = '';
    private $_password = '';
    private $_sftp = true;
    private $_connection = null;
    private $_errorMessage = "";

    public function __construct($server, $port, $user, $password)
    {
        $this->_server = $server;
        $this->_port = $port;
        $this->_user = $user;
        $this->_password = $password;
    }

    public function connect()
    {
        $this->_connection = ssh2_connect($this->_server, $this->_port);

        if (!$this->_connection) {
            $this->_errorMessage = "Could not connect to $this->_server on port $this->_port.";
            return false;
        }
        
        if (!ssh2_auth_password($this->_connection, $this->_user, $this->_password)) {
            $this->_errorMessage = "Could not authenticate";
            return false;
        }
        
        $this->_sftp = ssh2_sftp($this->_connection);
        if (!$this->_sftp) {
            $this->_errorMessage = "Could not initialize SFTP subsystem.";
            return false;
        }
        
        return true;
    }
    
    function uploadFile($localFile, $remoteFile)
    {
        $sftp = $this->_sftp;
        $stream = fopen("ssh2.sftp://$sftp$remoteFile", 'w');

        if (! $stream) {
            $this->_errorMessage = "Could not open file: $remoteFile";
            return false;
        }

        $data_to_send = file_get_contents($localFile);
        if ($data_to_send === false) {
            $this->_errorMessage = "Could not open local file: $localFile.";
            return false;
        }

        if (fwrite($stream, $data_to_send) === false) {
            $this->_errorMessage = "Could not send data from file: $localFile.";
            return false;
        }

        fclose($stream);
        
        return true;
    }
    
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    public function close()
    {
        if ($this->_connection) {
            unset($this->_connection);
            unset($this->_sftp);
            return true;
        }    
        return false;
    }

}