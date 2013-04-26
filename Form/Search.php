<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Search extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
    
        $this->getDecorator('HtmlTag')->clearOptions();
    }


    public function render(Zend_View_Interface $view = null)
    {
        $viewDefault = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getResource('layout')
                ->getView();

        $html = parent::render($view);

        if (!$viewDefault->popup) {
           
            $html .= '<div><script type="text/javascript">'
                . '$(function() {'
                    . '$("#' . $this->getId() . '").submit(function() {'
                        . 'self.location.href = ZendR.Url.serialize($(this).serialize(), "' . $viewDefault->url() . '");'
                        . 'return false;'
                    . '});'
                . '});'
                . '</script></div>';
        }

        return $html;
    }
}