<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_SuperEditor extends Zend_Form_Element_Textarea
{
    private $_enableFileManager = false;
    private $_renderScript = true;
    private $_urlUpload = '';
    
    private $_optionsJq = array(
        'lang'      => "'es'",
        'height'    => "200"
    );

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
    
    public function setUrlUpload($urlUpload)
    {
        $this->_urlUpload = $urlUpload;
        return $this;
    }

    public function setEnableFileManager($boolean)
    {
        $this->_enableFileManager = (boolean)$boolean;
        return $this;
    }

    public function setRenderScript($boolean)
    {
        $this->_renderScript = (boolean)$boolean;
        return $this;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->getView()->headScript()->appendFile($this->getView()->baseUrl('jquery/js/elrte.min.js'));
        $locale = Zend_Registry::get('Zend_Locale');
        if ($locale) {
            if ($locale->getLanguage() == 'es') {
                $this->getView()->headScript()->appendFile($this->getView()->baseUrl('jquery/js/i18n/elrte.es.js'));
            }
        }
        $this->getView()->headLink()->appendStylesheet($this->getView()->baseUrl('jquery/css/elrte.full.css'));
        
        $html = parent::render($view);
        if ($this->_renderScript) {
            $html .= '<script type="text/javascript">//<![CDATA[' . "\n"
                . '$(function() {'
                    . $this->obtenerScript()
                . '});'
            . '//]]></script>';
        }

        return $html;
    }

    public function obtenerScript($id = '')
    {
        if ($id == '') {
            $id = $this->getId();
        }
        $options = array();

        $noOptionFmOpen = true;
        foreach ($this->_optionsJq as $key => $value) {
            $options[] = $key . ':' . $value;

            if ($key == 'fmOpen') {
                $noOptionFmOpen = false;
            }
        }
        
        if ($noOptionFmOpen && $this->_enableFileManager) {
            $urlUpload = $this->_urlUpload;
            if ($urlUpload == '') {
                $urlUpload = $this->getView()->serverUrl($this->getView()->baseUrl('uploads/'));
            }
            $this->getView()->headScript()->appendFile($this->getView()->baseUrl('jquery/js/elfinder.min.js'));
            $this->getView()->headLink()->appendStylesheet($this->getView()->baseUrl('jquery/css/elfinder.css'));
            $options[] = 'fmOpen : function(callback) {'
                . '$(\'<div id="myFinder-' . $this->getId() . '" />\').elfinder({'
                    . 'url : "' . $this->getView()->baseUrl('jquery/connectors/php/connector.php?urlUpload=' 
                    . $urlUpload) . '",'
                    . 'dialog : {'
                        . 'width : 900,'
                        . 'modal : true,'
                        . 'title : \'Manejador de Archivos\''
                    . '},'
                    . 'closeOnEditorCallback : true,'
                    . 'editorCallback : callback'
                . '});'
            . '}';
        }

        return '$("#' . $id . '").elrte({' . implode(',', $options) . '});';
    }
}