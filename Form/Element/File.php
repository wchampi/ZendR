<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_File extends Zend_Form_Element_File
{
    protected $_fileName = '';
    
    public function setValue($value)
    {
        $this->_fileName = $value;
        return $this;
    }

    public function getValue()
    {
        $value = parent::getValue();
        if ($value == null && $this->_fileName != '') {
            return $this->_fileName;
        }

        return $value;
    }
    
    public function  isValid($value, $context = null)
    {
        if (parent::isValid($value, $context)) {
            if ($this->receive()) {
                if ($this->getFileName(null)) {
                    $this->rename(md5($this->getFileName(null) . time()) . '.' . $this->getExtension());
                    $this->_validated = true;
                    return true;
                }

                if (!$this->isRequired()) {
                    return true;
                }
            }
        }

        return false;
    }
    
    public function rename($newName)
    {
        rename(
            $this->getFileName(null, true),
            $this->getDestination() . '/' . $newName
        );
        $this->_value = $newName;
        return $this;
    }

    public function getExtension($filename = null)
    {
        if ($filename === null) {
            $filename = $this->getFileName(null, true);
            if (!file_exists($filename)) {
                return false;
            }
        }

		$tipos = explode(".", $filename);
		$extension = strtolower($tipos[count($tipos)-1]);
		return $extension;
	}
}