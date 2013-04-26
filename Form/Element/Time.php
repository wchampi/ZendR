<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Time extends Zend_Form_Element_Text
{
    public function init()
    {
        if (Zend_Registry::isRegistered('date_format')) {
            $this->setAttribs(array('size'=>10, 'maxlength' => '8'))
                ->addValidator('date', false, array('format' => 'H:i:s'));
        } else {
            $this->setAttribs(array('size'=>10, 'maxlength' => '8'))
                ->addValidator('date', false, array('format' => 'HH:mm:ss'));
        }
    }

    public function render(Zend_View_Interface $view = null)
    {
        $html = '<script>'
                . '$(function() {'
                    . '$("#' . $this->getId() . '").mask("99:99:99");'
                . '});'
            . '</script>'
            . parent::render($view);

        return $html;
    }
}