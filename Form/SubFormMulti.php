<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_SubFormMulti extends Zend_Form_SubForm
{
    public function enableBelongsToElements($enable = false)
    {
        $this->_enableBelongstoElements = (boolean) $enable;
    }

    public function runBelongsToElements()
    {
        foreach ($this->getElements() as $element) {
            $element->setBelongsTo($this->getElementsBelongTo());
        }
    }

    public function beValidated($data)
    {
        $validar = false;
        foreach ($data[$this->getName()] as $key => $value) {
            if (is_string($value)) {
                if (trim($value) != '') {
                    $validar = true;
                    break;
                }
            }
            if (is_array($value)) {
                foreach ($value as $val) {
                    if (trim($val) != '') {
                        $validar = true;
                        break;
                    }
                }
            }
        }

        if ($validar) {
            return true;
        }
        return false;
    }
    
    public function isValid($data)
    {
        if ($this->beValidated($data)) {
            return parent::isValid($data);
        } else {
            return true;
        }
    }
 
}