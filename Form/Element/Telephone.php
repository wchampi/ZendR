<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Telephone extends Zend_Form_Element_Xhtml
{
    public $helper = 'formTelephone';

    protected $_numberLength = 7;

    protected $_separator = '&nbsp;';

    public function  __construct($spec, $options = null)
    {
       $this->getView()->addHelperPath('ZendR/View/Helper', 'ZendR_View_Helper');
        parent::__construct($spec, $options);
    }

    public function getNumberLength()
    {
        return $this->_numberLength;
    }

    public function setNumberLength($numberLength)
    {
        $this->_numberLength = max(0, (int)$numberLength);
        return $this;
    }

    public function getSeparator()
    {
        return $this->_separator;
    }

    public function setSeparator($separator)
    {
        $this->_separator = trim($separator);
        return $this;
    }

    public function isValid($value)
    {
        $validTelephone = new ZendR_Validate_Telephone();
        $validTelephone->setNumberLength($this->getNumberLength());
        $this->addValidator($validTelephone);
        
        return parent::isValid($value);
    }

    public function setValue($value)
    {
        if ($value === null) {
            $value = '';
        }

        if (is_string($value)) {
            $this->_value = $value;
        } else if (is_array($value)) {
            if (isset($value['pais']) && isset($value['area']) && isset($value['numero'])) {
                if (trim($value['pais']) == '' && trim($value['area']) == '' && trim($value['numero']) == '') {
                    $this->_value = null;
                } else {
                    if (trim($value['area']) != '' || trim($value['numero']) != '') {
                        $validDigits = new Zend_Validate_Digits();
                        $pais = $validDigits->isValid($value['pais']) ? (int)$value['pais'] : $value['pais'];
                        $area = $validDigits->isValid($value['area']) ? (int)$value['area'] : $value['area'];
                        $numero = $validDigits->isValid($value['numero']) ? (int)$value['numero'] : $value['numero'];

                        $this->_value = $pais . '-' . $area . '-' . $numero;
                    } else {
                        $this->_value = null;
                    }
                }
            } else {
                throw new Exception('Invalid telephone value provided');
            }
        } else {
            throw new Exception('Invalid telephone value provided');
        }

        return $this;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->setAttrib('number_length', $this->getNumberLength());
        $this->setAttrib('separator', $this->getSeparator());
        return parent::render($view);
    }

}