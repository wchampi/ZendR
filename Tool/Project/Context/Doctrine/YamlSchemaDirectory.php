<?php

class ZendR_Tool_Project_Context_Doctrine_YamlSchemaDirectory extends Zend_Tool_Project_Context_Filesystem_Directory
{
    /**
     * @var string
     */
    protected $_filesystemName = 'schema';

    /**
     * @return string
     */
    public function getName()
    {
        return 'YamlSchemaDirectory';
    }
}
