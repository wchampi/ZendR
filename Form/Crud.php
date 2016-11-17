<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Crud extends ZendR_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setMethod('post');
        $this->_agregarBotones();
    }

    private function _agregarBotones()
    {
        $ver = $this->createElement('button', Zend_Registry::get('Zend_Translate')->_('view'))
        	->setLabel(Zend_Registry::get('Zend_Translate')->_('View'))
        	->setDecorators(ZendR_Decorators::factory('buttomLeft'))
        	->setAttrib('class', 'btnAction');
        $this->addElement($ver);

        $ver = $this->createElement('button', Zend_Registry::get('Zend_Translate')->_('edit'))
        	->setLabel(Zend_Registry::get('Zend_Translate')->_('Edit'))
        	->setDecorators(ZendR_Decorators::factory('buttomLeft'))
        	->setAttrib('class', 'btnAction');
        $this->addElement($ver);

        $regresar = $this->createElement('button', Zend_Registry::get('Zend_Translate')->_('list'))
        	->setLabel(Zend_Registry::get('Zend_Translate')->_('List'))
        	->setDecorators(ZendR_Decorators::factory('buttomLeft'))
        	->setAttrib('class', 'btnAction');
        $this->addElement($regresar);

        $eliminar = $this->createElement('button', Zend_Registry::get('Zend_Translate')->_('delete'))
        	->setLabel(Zend_Registry::get('Zend_Translate')->_('Delete'))
        	->setDecorators(ZendR_Decorators::factory('buttomRight'))
        	->setAttrib('class', 'btnActionSupr');
        $this->addElement($eliminar);

        $this->addDisplayGroup(
        	array(
                Zend_Registry::get('Zend_Translate')->_('accept'),
                Zend_Registry::get('Zend_Translate')->_('view'),
                Zend_Registry::get('Zend_Translate')->_('edit'),
                Zend_Registry::get('Zend_Translate')->_('list'),
                Zend_Registry::get('Zend_Translate')->_('delete')
            ),
        	'gButtons',
        	array(
        		'decorators' => ZendR_Decorators::factory('groupFieldsButton')
        	)
        );
    }

    public function initWeb()
    {
        $this->cleanDecorators();
    }

    public function removeButtonsCrud()
    {
        $this->removeElements(array(
                Zend_Registry::get('Zend_Translate')->_('view'),
                Zend_Registry::get('Zend_Translate')->_('edit'),
                Zend_Registry::get('Zend_Translate')->_('list'),
                Zend_Registry::get('Zend_Translate')->_('delete')
            )
        );
    }
}