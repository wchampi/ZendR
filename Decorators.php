<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Decorators
{
    public static function factory($decorator)
    {
        try {
            return self::$decorator();
        } catch (Exception $e) {
            throw new ZendR_Exception($e->getMessage());
        }
    }
    
	private static function field()
	{
		return  array(
			array('Label', array('separator'=>'', 'class' => 'label', 'disableFor' => 'true')),
            array('ViewHelper', array('tag' => null)),
            array('Description', array('tag' => 'span')),
            array('Errors', array('class' => 'txtErrors')),
			array('HtmlTag', array('tag' => 'div', 'class' => 'fields')),
		);
	}

    private static function fieldFileClean()
	{
		return  array(
            array('File'),
		);
	}

    private static function fieldClean()
	{
		return  array(
            array('ViewHelper', array('tag' => null)),
		);
	}

    private static function fieldRadio()
	{
		return  array(
            array('Label', array('separator'=>'', 'class' => 'label', 'disableFor' => 'true')),
			array('ViewHelper', array('tag' => null)),
            array('Description', array('tag' => 'span')),
			array('Errors', array('separator' => '<br /><br />', 'class' => 'txtErrors')),
			array('HtmlTag', array('tag' => 'div', 'class' => 'fields')),
		);
	}

    private static function fieldInline()
	{
		return array(
            array('Label', array('separator'=>'', 'disableFor' => 'true')),
			array('ViewHelper', array('tag' => null)),
			array('Errors', array()),
			array('HtmlTag', array('tag' => 'span')),
		);
	}

    private static function fieldFile()
    {
        return array(
           array('File'),
           array('Label', array('tag' => 'span', 'disableFor' => 'true')),
           array('Description', array('tag' => 'span')),
           array('Errors', array('class' => 'txtErrors')),
           array('HtmlTag', array('tag' => 'div', 'class' => 'fields'))
        );
    }

    private static function buttom()
	{
		return array(
			array('ViewHelper', array('tag' => null)),
			array('Errors', array()),
			array('HtmlTag', array('tag' => 'span')),
		);
	}

    private static function buttomLeft()
	{
		return array(
			array('ViewHelper', array('tag' => null)),
			array('Errors', array()),
			array('HtmlTag', array('tag' => 'div', 'class' => 'btnLeft')),
		);
	}

    private static function buttomRight()
	{
		return array(
			array('ViewHelper', array('tag' => null)),
			array('Errors', array()),
			array('HtmlTag', array('tag' => 'div', 'class' => 'btnRight')),
		);
	}

    private static function groupFields()
	{
		return array(
			'FormElements',
			array('HtmlTag', array('tag'=>'div')),
			'Fieldset'
		);
	}

    private static function groupFieldsButton()
	{
		return array(
			'FormElements',
			array('HtmlTag', array('tag'=>'div', 'class' => 'buttonContent'))
		);
	}
}