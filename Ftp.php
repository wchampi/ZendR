<?php

class ZendR_Ftp
{

    private $_server = 'localhost';
    private $_port = '21';
    private $_user = '';
    private $_password = '';
    private $_pasv = true;
    private $_ftp_stream = null;

    public function __construct($server, $port, $user, $password, $pasv = true)
    {
        $this->_server = $server;
        $this->_port = $port;
        $this->_user = $user;
        $this->_password = $password;
        $this->_pasv = $pasv;
    }

    public function connect()
    {
        $this->_ftp_stream = ftp_connect($this->_server, $this->_port);
        ftp_login($this->_ftp_stream, $this->_user, $this->_password);
        ftp_pasv($this->_ftp_stream, $this->_pasv);
        return $this->_ftp_stream;
    }
    
    function uploadFile($localFile, $remoteFile)
    {
        if ($this->_ftp_stream) {
            return ftp_put($this->_ftp_stream, $remoteFile, $localFile, FTP_BINARY);
        } else {
            return false;
        }
    }
    
    public function close()
    {
        if ($this->_ftp_stream) {
            return ftp_quit($this->_ftp_stream);
        }    
        return false;
    }

}