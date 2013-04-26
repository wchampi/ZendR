<?php

class ZendR_Tool_Project_Context_Doctrine_DataFixturesDirectory extends Zend_Tool_Project_Context_Filesystem_Directory
{
    /**
     * @var string
     */
    protected $_filesystemName = 'fixtures';

    /**
     * @return string
     */
    public function getName()
    {
        return 'DataFixturesDirectory';
    }
}
