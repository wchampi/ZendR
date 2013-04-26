<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Search extends Zend_Form_Element_Text
{
    private $_urlSearch = null;

    private $_valueDescription = '';

    public function setUrlSearch($urlSearch)
    {
        $this->_urlSearch = $urlSearch;
        return $this;
    }

    public function setValueDescription($valueDescription)
    {
        $this->_valueDescription = $valueDescription;
        return $this;
    }

    public function render(Zend_View_Interface $view = null)
    {
        if (is_numeric($this->_urlSearch)) {
            throw new Exception('Se tiene que ingresar urlSearch');
        }

        $viewDefault = new Zend_View();

        $html = parent::render($view);
        $html .= '<script type="text/javascript">//<![CDATA[' . "\n"
			. '$(function() {'
            . '$("#' . $this->getId() . '").after(\''
            . '&nbsp;&nbsp;&nbsp;<span id="' . $this->getId() . '-select">' . $this->_valueDescription . '</span>&nbsp;&nbsp;&nbsp;'
            . '<a href="javascript:void(0)" id="' . $this->getId() . 'search-a-href">'
            . '<img src="' . $viewDefault->baseUrl('zendr/css/graphics/ico_search.gif') . '" alt="" border="0" width="17px" />'
            . '</a><div id="' . $this->getId() . '-search" style="display:none"></div>'
            . '\');'
            . '$("#' . $this->getId() . 'search-a-href").click(function () {'
            . 'ZendR.Search.searchItem("' . $this->getId() . '", "' . $viewDefault->baseUrl($this->_urlSearch . '/popup/true') . '")'
            . '});'
            . '$("#' . $this->getId() . '-search").dialog({'
            . 'bgiframe: true,'
            . 'autoOpen: false,'
            . 'width: "1000px",'
            . 'modal: true'
            . '});'
			. '});'
            . '//]]></script>';

        return $html;
    }
}