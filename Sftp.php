<?php

class ZendR_Sftp
{

    private $_server = 'localhost';
    private $_port = '22';
    private $_user = '';
    private $_password = '';
    private $_sftp = true;
    private $_connection = null;

    public function __construct($server, $port, $user, $password)
    {
        $this->_server = $server;
        $this->_port = $port;
        $this->_user = $user;
        $this->_password = $password;
    }

    public function connect()
    {
        $this->_connection = @ssh2_connect($this->_server, $this->_port);
        if (!$this->_connection)
            throw new Exception("Could not connect to $this->_server on port $this->_port.");
        
        if (! @ssh2_auth_password($this->_connection, $this->_user, $this->_password))
            throw new Exception("Could not authenticate with username $this->_user " .
                                "and password $this->_password.");

        $this->_sftp = @ssh2_sftp($this->_connection);
        if (!$this->_sftp)
            throw new Exception("Could not initialize SFTP subsystem.");
    }
    
    function uploadFile($localFile, $remoteFile)
    {
        $sftp = $this->_sftp;
        $stream = @fopen("ssh2.sftp://$sftp$remoteFile", 'w');

        if (! $stream)
            throw new Exception("Could not open file: $remoteFile");

        $data_to_send = @file_get_contents($localFile);
        if ($data_to_send === false)
            throw new Exception("Could not open local file: $localFile.");

        if (@fwrite($stream, $data_to_send) === false)
            throw new Exception("Could not send data from file: $localFile.");

        @fclose($stream);
    }
    
    public function close()
    {
        if ($this->_ftp_stream) {
            return ftp_quit($this->_ftp_stream);
        }    
        return false;
    }

}