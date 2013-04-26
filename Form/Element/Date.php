<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Date extends Zend_Form_Element_Text
{
    private $_disableJq = false;
    
    private $_optionsJq = array (
        'changeMonth' => 'true',
        'changeYear' => 'true',
    );

    public function  __construct($spec, $options = null)
    {
        $this->getView()->addHelperPath('ZendR/View/Helper', 'ZendR_View_Helper');
        parent::__construct($spec, $options);
    }
    
    public function init()
    {
        $validator = new Zend_Validate_Date(Zend_Registry::get('date_format'));

        $this->setAttribs(array('size'=>12, 'maxlength' => '10'))
            ->addValidator($validator);
    }

    public function setDisableJq($boolean)
    {
        $this->_disableJq = $boolean;
        return $this;
    }

    public function setValue($value)
    {
        if ($value === null) {
            $value = '';
        }

        if (is_string($value)) {
            $this->_value = $value;
        } else if (is_array($value)) {
            if (isset($value['day']) && isset($value['month']) && isset($value['year'])) {
                if (trim($value['day']) == '' && trim($value['month']) == '' && trim($value['year']) == '') {
                    $this->_value = null;
                } else {
                    $dateFormat = 'd/m/Y';
                    if (Zend_Registry::isRegistered('date_format')) {
                        $dateFormat = Zend_Registry::get('date_format');
                    }
                    $this->_value = str_replace(
                        array('d','m','Y'),
                        array(trim($value['day']), trim($value['month']), trim($value['year'])),
                        $dateFormat
                    );
                }
            } else {
                throw new Exception('Invalid date value provided');
            }
        } else {
            throw new Exception('Invalid date value provided');
        }

        return $this;
    }
    
    public function addOptionJq($key, $value)
    {
        $this->_optionsJq[$key] = $value;
        return $this;
    }

    public function removeOptionJq($key)
    {
        if (isset ($this->_optionsJq[$key])) {
            unset ($this->_optionsJq[$key]);
        }
        return $this;
    }

    public function setOptionsJq(array $options)
    {
        $this->_optionsJq = $options;
        return $this;
    }

    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_disableJq) {
            $this->helper = 'formDate';
            return parent::render($view);
        } else {
            $options = array();
            foreach ($this->_optionsJq as $key => $value) {
                $options[] = $key . ':' . $value;
            }

            $html = '<script type="text/javascript">'
                    . '$(function() {'
                        . '$("#' . $this->getId() . '").datepicker({'
                            . implode(',', $options)
                        . '});'
                    . '});'
                . '</script>'
                . parent::render($view);


            return $html;
        }
    }
}