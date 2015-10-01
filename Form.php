<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form extends Zend_Form
{
    private $_hashName = '';
    
    public function __construct($options = null)
    {
        parent::__construct($options);

        $aceptar = $this->createElement('submit', Zend_Registry::get('Zend_Translate')->_('accept'))
        	->setLabel(Zend_Registry::get('Zend_Translate')->_('Accept'))
        	->setDecorators(ZendR_Decorators::factory('buttomLeft'))
        	->setAttrib('class', 'btnAction');
        $this->addElement($aceptar);

        $this->addDisplayGroup(
        	array(Zend_Registry::get('Zend_Translate')->_('accept')),
        	'gButtons',
        	array(
        		'decorators' => ZendR_Decorators::factory('groupFieldsButton')
        	)
        );
        
        if ($this->getName() == '') {
            $hash = new ZendR_Form_Element_Hash();
        } else {
            $hash = new ZendR_Form_Element_Hash($this->getName() . '_csrf_token');
        }
        $hash->setTimeout(864000);
        $hash->setDecorators(ZendR_Decorators::factory('field'));
        $this->addElement($hash);
        $this->_hashName = $hash->getName();
        foreach ($this->getElements() as $element) {
            $htmlTag = $element->getDecorator('HtmlTag');
            if ($htmlTag) {
                $element->getDecorator('HtmlTag')->setOption('id', $element->getName() . '-element');
            }
        }

        $this->getDecorator('HtmlTag')->clearOptions();
    }

    public function  cleanDecorators() {
        foreach ($this->getElements() as $element) {
            if ($element instanceof Zend_Form_Element_File) {
                $element->setDecorators(ZendR_Decorators::factory('fieldFileClean'));
            } else {
                $element->setDecorators(ZendR_Decorators::factory('fieldClean'));
            }
        }
    }

    public function getElementHash()
    {
        return $this->getElement($this->_hashName);
    }

    public function removeElementHash()
    {
        $this->removeElement($this->_hashName);
    }

    public function removeElements($elements = null, $excepto = null)
    {
        if (!is_array($excepto)) {
            $excepto = array();
        }
        
        if ($elements == null) {
            foreach ($this->getElements() as $element) {
                if (!in_array($element->getName(), $excepto)) {
                    $this->removeElement($element->getName());
                }
            }
        } else {
            if (is_array($elements)) {
                $elements = array_diff($elements, $excepto);
                foreach ($elements as $nameElement) {
                    $this->removeElement($nameElement);
                }
            }
        }
    }
}