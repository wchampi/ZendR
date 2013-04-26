<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Core extends Doctrine_Core
{
    /**
     * @var int
     */
    const MODEL_LOADING_ZEND = 4;

    /**
     * @var array
     */
    private static $_modelDirs = null;

    public static function resetModelDirectories()
    {
        self::$_modelDirs = null;
    }

    /**
     * @param array|string $directories
     * @return array
     */
    public static function loadModels($directories, $modelLoading = null, $classPrefix = null)
    {
        $manager = Doctrine_Manager::getInstance();

        $modelLoading = ($modelLoading != null) ? $manager->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING) : $modelLoading;

        $zendStyles = array(self::MODEL_LOADING_ZEND);
        if (in_array($modelLoading, $zendStyles)) {
            return self::loadAllZendModels();
        } else {
            return parent::loadModels($directories, $modelLoading, $classPrefix);
        }
    }

    /**
     *
     * @return array
     */
    public static function getAllModelDirectories()
    {
        $manager = Doctrine_Manager::getInstance();
        $modelLoading = $manager->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING);

        if (self::$_modelDirs == null) {
            $manager = Doctrine_Manager::getInstance();

            $front = Zend_Controller_Front::getInstance();
            $modules = $front->getControllerDirectory();
            $modelDirectories = array();

            // For all model styles make sure that they end with a / in the directory name!!
            if ($modelLoading == self::MODEL_LOADING_ZEND) {
                $controllerDirName = $front->getModuleControllerDirectoryName();
                foreach ((array)$modules AS $module => $controllerDir) {
                    $modelDir = str_replace( $controllerDirName, '',  $controllerDir) .
                                DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
                    $modelDirectories[$module] = $modelDir;
                }
            } else {
                throw ZendR_Doctrine_Exception::invalidZendStyle();
            }
            self::$_modelDirs = $modelDirectories;
        }
        return self::$_modelDirs;
    }

    /**
     *
     * @param array|string $moduleDirectories
     * @return array
     */
    public static function loadAllZendModels()
    {
        $loadedModels = null;
        if (defined('APPLICATION_PATH')) {
            $fileModels = APPLICATION_PATH . '/configs/autoload/models.json';
            if (file_exists($fileModels)) {
                $loadedModels = json_decode(file_get_contents($fileModels));
            }
        }
            
            
        if ($loadedModels == null) {
            $loadedModels = new stdClass();
            foreach (self::getAllModelDirectories() AS $module => $modelDir) {
                if (!file_exists($modelDir)) {
                    continue;
                }

                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelDir),
                            RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($it AS $file) {
                    if (substr($file->getFileName(), -4) !== ".php") {
                        continue;
                    }

                    $className = str_replace($modelDir, "", $file->getPathName());
                    $className = str_replace('generated' . DIRECTORY_SEPARATOR, "", $className);
                    $className = str_replace('table' . DIRECTORY_SEPARATOR, "", $className);
                    $className = substr($className, 0, strpos($className, '.'));
                    $loadedModels->$className = $file->getPathName();
                }
            }
        }
        #echo json_encode($loadedModels);exit;
        Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);

        $zendRAutoload = new ZendR_Doctrine_Autoloader($loadedModels);
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader($zendRAutoload);

        return $loadedModels;
    }


    /**
     *
     * @param  string $name
     * @return string
     */
    static protected function _formatModuleName($name)
    {
        $name = strtolower($name);
        $name = str_replace(array('-', '.'), ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }

    public function generateCrudFromModel($classModel, $moduleName, $force)
    {
        $modules = ZendR_Doctrine_Core::getAllModelDirectories();
        if ($moduleName === null) {
            $modelFiles = Doctrine_Core::getLoadedModelFiles();
            $path = dirname(dirname($modelFiles[$classModel]));
            foreach ($modules AS $module => $directory) {
                if (strpos($directory, $path) !== false) {
                    $moduleName = $module;
                }
            }
        } else {
            $path = '';
            foreach ($modules AS $module => $directory) {
                if ($moduleName == $module) {
                    $path = dirname($directory);
                    break;
                }
            }

            if ($path == '') {
                throw ZendR_Doctrine_Exception::unknownModule($moduleName, $classModel);
            }
        }
		
		$importSchema = new ZendR_Doctrine_Import_Crud_Schema($classModel, $moduleName, $path, $force);
		$importSchema->builder();
    }

    public function generateFormFromModel($classModel, $moduleName, $force)
    {
        $modules = ZendR_Doctrine_Core::getAllModelDirectories();
        if ($moduleName === null) {
            $modelFiles = Doctrine_Core::getLoadedModelFiles();
            $path = dirname(dirname($modelFiles[$classModel]));
            foreach ($modules AS $module => $directory) {
                if (strpos($directory, $path) !== false) {
                    $moduleName = $module;
                }
            }
        } else {
            $path = '';
            foreach ($modules AS $module => $directory) {
                if ($moduleName == $module) {
                    $path = dirname($directory);
                    break;
                }
            }

            if ($path == '') {
                throw ZendR_Doctrine_Exception::unknownModule($moduleName, $classModel);
            }
        }

		$importSchema = new ZendR_Doctrine_Import_Crud_Schema($classModel, $moduleName, $path, $force);
		$builderForm = new ZendR_Doctrine_Import_Crud_BuilderForm($importSchema);
		$builderForm->build();
    }
}
