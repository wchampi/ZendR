<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Form_Element_Hash extends Zend_Form_Element_Hash
{
    public function  __construct($spec = 'csrf_token')
    {
        parent::__construct($spec, array('salt' => 's3cr3t%Ek@on9#'));
    }
}