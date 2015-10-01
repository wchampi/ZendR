<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_ExpirationDate extends Zend_Form_Element_Xhtml
{
    public $helper = 'formExpirationDate';
    
    public function  __construct($spec, $options = null)
    {
       $this->getView()->addHelperPath('ZendR/View/Helper', 'ZendR_View_Helper');
        parent::__construct($spec, $options);
    }

    public function init()
    {

    }

    public function setValue($value)
    {
        if ($value === null) {
            $value = '';
        }

        if (is_string($value)) {
            $this->_value = $value;
        } else if (is_array($value)) {
            if (isset($value['month']) && isset($value['year'])) {
                if (trim($value['month']) == '' && trim($value['year']) == '') {
                    $this->_value = null;
                } else {
                    $this->_value = trim($value['month']) . '/' . trim($value['year']);
                }
            } else {
                throw new Exception('Invalid date value provided');
            }
        } else {
            throw new Exception('Invalid date value provided');
        }

        return $this;
    }

}