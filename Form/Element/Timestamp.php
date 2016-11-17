<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Timestamp extends Zend_Form_Element_Xhtml
{
    public $helper = 'formTimestamp';
    
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
        $this->addValidator('date', false, Zend_Locale_Format::getDateFormat() . ' HH:mm:ss');
    }
    
    public function setValue($value)
    {
        if ($value === null) {
            $value = '';
        }
        
        if (is_string($value)) {
            $this->_value = $value;
        } else if (is_array($value)) {
            if (isset($value['date']) && isset($value['time'])) {
                if (trim($value['date']) == '' && trim($value['time']) == '') {
                    $this->_value = null;
                } else {
                    $this->_value = trim($value['date']) . ' ' . trim($value['time']);
                }
            } else {
                throw new Exception('Invalid datetime value provided');
            }
        } else {
            throw new Exception('Invalid datetime value provided');
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
        $options = array();
        foreach ($this->_optionsJq as $key => $value) {
            $options[] = $key . ':' . $value;
        }

        $html = '<script type="text/javascript">'
                . '$(function() {'
                    . '$("#' . $this->getId() . '-date").datepicker({'
                        . implode(',', $options)
                    . '});'
                    . '$("#' . $this->getId() . '-time").mask("99:99:99");'
                . '});'
            . '</script>'
            . parent::render($view);

        return $html;
    }
}