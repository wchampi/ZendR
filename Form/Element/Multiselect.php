<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Multiselect extends Zend_Form_Element_Multiselect
{
    public function render(Zend_View_Interface $view = null)
    {
        $this->getView()->headScript()->appendFile($this->getView()->baseUrl('zendr/js/django/jsi18n.js'));
        $this->getView()->headScript()->appendFile($this->getView()->baseUrl('zendr/js/django/core.js'));
        $this->getView()->headScript()->appendFile($this->getView()->baseUrl('zendr/js/django/SelectBox.js'));
        $this->getView()->headScript()->appendFile($this->getView()->baseUrl('zendr/js/django/SelectFilter2.js'));
        
        $html = parent::render($view);
        $html .= '<script type="text/javascript">'
			. '$(function() {'
            . 'addEvent(window, "load", function(e) {SelectFilter.init("' . $this->getName() . '", "' 
            . ucwords(str_replace('_', ' ', $this->getName())) . '", 0,  "' . $this->getView()->baseUrl() . '/"); });'
			. '});'
            . '</script>';

        return $html;
    }
}