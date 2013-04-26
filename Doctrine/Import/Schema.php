<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Schema extends Doctrine_Import_Schema
{
    /**
     * @var Doctrine_Import_Builder
     */
    protected $_builder = null;

    /**
     * @var array
     */
    protected $_modules = array();

    /**
     * @var array
     */
    protected $_listener = null;

    /**
     *
     * @param  string $schema       The file containing the XML schema
     * @param  string $format       Format of the schema file
     * @param  string $directory    The directory where the Doctrine_Record class will be written
     * @param  array  $models       Optional array of models to import
     *
     * @return void
     */
    public function importSchema($schema, $format = 'yml', $directory = null, $models = array())
    {
        $manager = Doctrine_Manager::getInstance();
        $modelLoading = $manager->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING);

        $zendStyles = array(ZendR_Doctrine_Core::MODEL_LOADING_ZEND);
        if (!in_array($modelLoading, $zendStyles)) {
            throw new ZendR_Doctrine_Exception(
                "Can't use ZendR_Doctrine_Import_Schema with Doctrine_Core::ATTR_MODEL_LOADING not equal to 4 (Zend)."
            );
        }

		$schemaContent = '';
		$modules = array();
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($schema),
                                                      RecursiveIteratorIterator::LEAVES_ONLY);
		foreach ($files as $file) {
			$ext = explode('.', $file->getFileName());
			if (end($ext) === $format) {
				$moduleName = $ext[0];
				$modules[$moduleName] = $this->buildSchema($file->getPathName(), $format);
				$schemaContent .= "\n" . file_get_contents($file->getPathName());
			}
		}
		$fileSchemaTmp = realpath(dirname(__FILE__)) . '/tmp.yml';
		file_put_contents($fileSchemaTmp, $schemaContent);
		$schemaTotal = $this->buildSchema($fileSchemaTmp, $format);
		unlink($fileSchemaTmp);
		
        if (count($modules) == 0) {
            throw new Doctrine_Import_Exception(
                sprintf('No records found for schema "' . $format . '" found in ' . implode(", ", (array)$schema))
            );
        }

        $builder = $this->_getBuilder();
        $builder->setOptions($this->getOptions());

        $this->_initModules();
        foreach ($modules as $moduleName => $records) {
			foreach ($records as $name => $definition) {
				if (!isset($definition['className'])) {
					continue;
				}
				if (!empty($models) && !in_array($definition['className'], $models)) {
					continue;
				}
				$this->_buildRecord($builder, $schemaTotal[$name], $moduleName);
			}
        }

        if ($this->_listener) {
			$this->_listener->notifyImportCompleted();
        }
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        $options['generateTableClasses'] = true;
        return $options;
    }

    /**
     * @param Doctrine_Import_Builder $builder
     * @return void
     */
    public function setBuilder(Doctrine_Import_Builder $builder)
    {
        $this->_builder = $builder;
    }

    /**
     * @return Doctrine_ImportBuilder
     */
    protected function _getBuilder()
    {
        if ($this->_builder == null) {
            $this->_builder = new ZendR_Doctrine_Import_Builder();
        }
        return $this->_builder;
    }

    /**
     * @return void
     */
    protected function _initModules()
    {
        $this->_modules = ZendR_Doctrine_Core::getAllModelDirectories();
    }

    /**
     * @param Doctrine_Import_Builder $builder
     * @param array $definition
     */
    protected function _buildRecord($builder, $definition, $moduleName)
    {
        if (isset($definition['className'])) {
            $className = $definition['className'];

            if (trim($className) != '') {
                if (!isset($this->_modules[$moduleName])) {
                    throw ZendR_Doctrine_Exception::unknownModule($moduleName, $className);
                }

                $builder->setTargetPath($this->_modules[$moduleName]);
                $builder->buildRecord($definition);

                if ($this->_listener) {
                    $this->_listener->notifyRecordBuilt($className, $moduleName);
                }
            }
        }
    }

    /**
     * @param ZendR_Doctrine_Import_Listener_Interface $listener
     */
    public function setListener(ZendR_Doctrine_Import_Listener_Interface $listener)
    {
        $this->_listener = $listener;
    }
}