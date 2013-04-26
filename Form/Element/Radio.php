<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Radio extends Zend_Form_Element_Radio
{
    public function  __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);

        Zend_Registry::get('view')->registerHelper(new ZendR_View_Helper_FormRadio(), 'formRadio');

        $this->setSeparator('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    }
}