<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_View_Helper_FormTimestamp extends Zend_View_Helper_FormElement
{
 
    public function formTimestamp($name, $value = null, $attribs = null,
        $options = null)
    {
        $valueArr = explode(' ', $value);
        $view = $this->view;
        
        $paramsDate = array('size' => 12, 'maxlength' => 10);
        $paramsTime = array('size' => 10, 'maxlength' => 8);

        $xhtml = $view->formText($name . '[date]', isset($valueArr[0]) ? $valueArr[0] : '', $paramsDate)
            . ' ' . $view->formText($name . '[time]', isset($valueArr[1]) ? $valueArr[1] : '', $paramsTime);
        return $xhtml;
    }
}
