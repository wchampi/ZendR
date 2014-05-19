<?php

class ZendR_Ftp
{

    private $_server = 'localhost';
    private $_port = '21';
    private $_user = '';
    private $_password = '';
    private $_pasv = true;
    private $_curl = true;
    private $_ftp_stream = null;

    public function __construct($server, $port, $user, $password, $pasv = true, $curl = false)
    {
        $this->_server = $server;
        $this->_port = $port;
        $this->_user = $user;
        $this->_password = $password;
        $this->_pasv = $pasv;
        $this->_curl = $curl;
    }

    public function connect()
    {
        if ($this->_curl) {
            $this->_ftp_stream = curl_init();
        } else {
            $this->_ftp_stream = ftp_connect($this->_server, $this->_port);
            ftp_login($this->_ftp_stream, $this->_user, $this->_password);
            ftp_pasv($this->_ftp_stream, $this->_pasv);
        }  
        return $this->_ftp_stream;
    }
    
    public function uploadFile($localFile, $remoteFile)
    {
        if ($this->_ftp_stream) {
            if ($this->_curl) {
                curl_setopt($this->_ftp_stream, CURLOPT_URL, 'ftp://' . $this->_server . $remoteFile);
                curl_setopt($this->_ftp_stream, CURLOPT_USERPWD,  $this->_user . ":" . $this->_password);
                curl_setopt($this->_ftp_stream, CURLOPT_UPLOAD, 1);

                $fp = fopen($localFile, 'r');
                curl_setopt($this->_ftp_stream, CURLOPT_INFILE, $fp);
                curl_setopt($this->_ftp_stream, CURLOPT_INFILESIZE, filesize($localFile));
                curl_exec ($this->_ftp_stream);

                $error_no = curl_errno($this->_ftp_stream);
                if ($error_no == 0) {
                    return true;
                }
            } else {
                return ftp_put($this->_ftp_stream, $remoteFile, $localFile, FTP_BINARY);
            }    
        }
        return false;
    }
    
    public function close()
    {
        if ($this->_ftp_stream) {
            if ($this->_curl) {
                curl_close ($this->_ftp_stream);
            } else {    
                return ftp_close($this->_ftp_stream);
            }    
        }    
        return false;
    }

}