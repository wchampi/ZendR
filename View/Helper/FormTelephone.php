<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_View_Helper_FormTelephone extends Zend_View_Helper_FormElement
{
 
    public function formTelephone($name, $value = null, $attribs = null,
        $options = null)
    {
        $valueArr = explode('-', $value);
        $view = $this->view;
        
        $paramsPais = array('size' => 2, 'maxlength' => 4);
        $paramsArea = array('size' => 2, 'maxlength' => 3);
        $paramsNumero = array('size' => $attribs['number_length'], 'maxlength' => $attribs['number_length']);

        $xhtml = $view->formText($name . '[pais]', $valueArr[0], $paramsPais)
            . $attribs['separator'] . $view->formText($name . '[area]', $valueArr[1], $paramsArea)
            . $attribs['separator'] . $view->formText($name . '[numero]', $valueArr[2], $paramsNumero);
        return $xhtml;
    }
}
