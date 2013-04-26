<?php

class ZendR_Tool_Project_Context_Doctrine_MigrationsDirectory extends Zend_Tool_Project_Context_Filesystem_Directory
{
    /**
     * @var string
     */
    protected $_filesystemName = 'migrations';

    /**
     * @return string
     */
    public function getName()
    {
        return 'MigrationsDirectory';
    }
}
