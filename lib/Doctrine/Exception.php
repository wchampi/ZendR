<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Exception extends Zend_Exception
{
    public static function doctrineNotFound()
    {
        return new self('Could not find Doctrine library in project and include path.');
    }

    /**
     * @param string $className
     * @return ZendR_Doctrine_Exception
     */
    public static function invalidZendModel($className)
    {
        return new self('Found an invalid model class '.$className.' which is not following the required Zend style, i.e '.
            'Model_ClassName for the default module or ModuleName_Model_ClassName for the models in non-default modules.');
    }

    public static function unknownModule($moduleName, $className)
    {
        return new self(
            "Unknown Zend Controller Module '".$moduleName."' inflected from model class '".$className."'. ".
            "Have you configured your front-controller to include modules?");
    }

    public static function invalidLibraryPath($path)
    {
        return new self("Invalid library path specified, " . $path . " could not be found.");
    }

    public static function libraryPathMissing()
    {
        return new self("To use the ZEND_SINGLE_LIBRARY model loading mode you have to specify ".
            "a library path using ZFDoctrine_Core::setSingleLibraryPath()");
    }

    public static function invalidZendStyle()
    {
        return new self('Invalid Zend Model Loading Style configured.');
    }
}