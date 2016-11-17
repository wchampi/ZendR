<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

interface ZendR_Doctrine_Import_Listener_Interface
{
    /**
     * @param string $className
     * @param string $moduleName
     */
    public function notifyRecordBuilt($className, $moduleName);

    /**
     * @return void
     */
    public function notifyImportCompleted();
}