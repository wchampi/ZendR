<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Tool_Project_Provider_ZendR extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{

    /**
     * @var ZendR_Doctrine_Registry
     */
    protected $_doctrineRegistry = null;

    /**
     * @return array
     */
    public function getContextClasses()
    {
        return array(
            'ZendR_Tool_Project_Context_Doctrine_DataFixturesDirectory',
            'ZendR_Tool_Project_Context_Doctrine_MigrationsDirectory',
            'ZendR_Tool_Project_Context_Doctrine_SqlDirectory',
            'ZendR_Tool_Project_Context_Doctrine_YamlSchemaDirectory',
        );
    }

    /**
     * @param  string $dsn
     * @param  bool $withResourceDirectories
     * @return void
     */
    public function generarConfiguracion($dsn)
    {
        $profile = $this->_loadProfileRequired();

        $applicationConfigResource = $profile->search('ApplicationConfigFile');

        if (!$applicationConfigResource) {
            throw new Zend_Tool_Project_Exception('A project with an application config file is required to use this provider.');
        }

        $zc = $applicationConfigResource->getAsZendConfig();

        if (isset($zc->resources) && isset($zf->resources->doctrine)) {
            $this->_registry->getResponse()->appendContent('A Doctrine resource already exists in this project\'s application configuration file.');
            return;
        }

        /* @var $applicationConfigResource Zend_Tool_Project_Context_Zf_ApplicationConfigFile */
        $applicationConfigResource->addStringItem('resources.doctrine.connections.default.dsn', $dsn, 'production', '"'.$dsn.'"');
        $applicationConfigResource->create();
		$applicationConfigResource->addStringItem('resources.doctrine.connections.default.attributes.attr_default_table_charset', 'utf8', 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('pluginpaths.ZendR_Application_Resource', 'ZendR/Application/Resource', 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('autoloadernamespaces[]', "Doctrine", 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('autoloadernamespaces[]', "ZendR", 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('resources.locale.default', "es_PE", 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('resources.locale.force', "true", 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('resources.view.doctype', "XHTML1_STRICT", 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('resources.apps.date.date_format', "d/m/Y", 'production');
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('includePaths.application', "APPLICATION_PATH", 'production', false);
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('includePaths.doctrine', '"D:\library\Doctrine\1.2"', 'production', false);
        $applicationConfigResource->create();

        if ($this->_registry->getRequest()->isPretend()) {
            $this->_print('Would enable Doctrine support by adding resource string.');
        } else {
            $this->_print('Enabled Doctrine Zend_Application resource in project.', array('color' => 'green'));
        }

        $configsDirectory = $profile->search(array('configsDirectory'));

        if ($configsDirectory == null) {
            throw new Exception("No Config directory in Zend Tool Project.");
        }

        $globalResources = array('YamlSchemaDirectory', 'DataFixturesDirectory', 'MigrationsDirectory', 'SqlDirectory');
        $changes = false;
        foreach ($globalResources AS $resourceName) {
            if (!$profile->search(array('configsDirectory', $resourceName))) {
                if ($this->_registry->getRequest()->isPretend()) {
                    $this->_print("Would add ".$resourcenName." to the application config directory.");
                } else {
                    $resource = $configsDirectory->createResource($resourceName);
                    if (!$resource->exists()) {
                        $resource->create();
                        $this->_print('Created Resource: '.$resourceName, array('color' => 'green'));
                    } else {
                        $this->_print('Registered Resource: '.$resourceName, array('color' => 'green'));
                    }
                    $changes = true;
                }
            }
        }

        if ($changes) {
            $profile->storeToFile();
        }
    }

    public function construirBd($force = false, $load=false, $reload=false)
    {
        if ($reload) {
            $this->dropDatabase($force);
        }
        $this->_createDatabase();
        $this->_createTables();
        if ($load) {
            $this->loadData(false);
        }
    }

    private function _createDatabase()
    {
        $doctrine = $this->_getDoctrineRegistry();

        foreach ($doctrine->getConnections() as $name => $connection) {
            try {
                $connection->createDatabase();
                $this->_print(
                    "Successfully created database for connection named '" . $name . "'",
                    array('color' => 'green')
                );
            } catch (Exception $e) {
                $this->_print(
                    "Error creating database for connection '".$name."': ".$e->getMessage(),
                    array('color' => array('white', 'bgRed'))
                );
            }
        }
    }

    private function _dropDatabase($force = false)
    {
        if ($force == false) {
            $confirmed = $this->_registry
                              ->getClient()
                              ->promptInteractiveInput('Are you sure you wish to irrevocably drop your databases? (y/n)')->getContent();
            if (strtolower($confirmed) != "y") {
                $this->_print('Dropping databases was aborted.');
                return;
            }
        }

        $doctrine = $this->_getDoctrineRegistry();

        foreach ($doctrine->getConnections() as $name => $connection) {
            try {
                $connection->dropDatabase();
                $this->_print("Successfully dropped database for connection named '" . $name . "'", array('color' => 'green'));
            } catch (Exception $e) {
                $this->_print("Error dropping database '".$name."': ". $e->getMessage(), array('color' => array('white', 'bgRed')));
            }
        }
    }

    private function _createTables()
    {
        $this->_initDoctrineResource();
        $modelsLoad = $this->_loadDoctrineModels();

        try {
            $models = array();
            foreach ($modelsLoad as $model => $modelPath) {
                $models[] = $model;
            }

            $models = ZendR_Doctrine_Core::filterInvalidModels($models);
            $export = Doctrine_Manager::connection()->export;

            $queries = $export->exportSortedClassesSql($models);
            $countSql = 0;
            foreach ($queries as $connectionName => $sql) {
                $countSql += count($sql);
            }

            if ($countSql) {
                $export->exportClasses($models);

                $this->_print("Successfully created tables from model.", array('color' => 'green'));
                $this->_print("Executing " . $countSql . " queries for " . count($queries) . " connections.", array('color' => 'green'));
            } else {
                $this->_print("No sql queries found to create tables from.", array('color' => array('white', 'bgRed')));
                $this->_print("Have you generated a model from your YAML schema?", array('color' => array('white', 'bgRed')));
            }
        } catch(Exception $e) {
            $this->_print("Error while creating tables from model: ".$e->getMessage(), array('color' => array('white', 'bgRed')));

            if ($this->_registry->getRequest()->isDebug()) {
                $this->_print($e->getTraceAsString());
            }
        }
    }


    public function generarSql($connectionName = 'default')
    {
        $modelsLoad = $this->_loadDoctrineModels();
        $models = array();
        foreach ($modelsLoad as $model => $modelPath) {
            $models[] = $model;
        }


        $sqlDir = $this->_getSqlDirectoryPath();

        $conn = Doctrine_Manager::getInstance()->getConnection($connectionName);
        if (!$conn) {
            $this->_print("Connection: $connectionName not exists", array('color' => array('white', 'bgRed')));
        }

        $sql = $conn->export->exportClassesSql($models);

        $sqlSchemaFile = $sqlDir . DIRECTORY_SEPARATOR . $connectionName . "_".date('Ymd_His').".sql";
        file_put_contents($sqlSchemaFile, implode("\n", $sql));

        $this->_print('Successfully written SQL for the current schema to disc.', array('color' => 'green'));
        $this->_print('Destination File: '.$sqlSchemaFile);
    }

	public function cargarRegistrosALaBd($append = false)
    {
        $this->_loadDoctrineModels();
        Doctrine_Core::loadData($this->_getDataFixtureDirectoryPath(), $append);

        $this->_print('Successfully loaded data from fixture.', array('color' => 'green'));
    }

    public function traerRegistrosDeLaBd($individualFiles = false)
    {
        $this->_loadDoctrineModels();

        $fixtureDir = $this->_getDataFixtureDirectoryPath();

        Doctrine_Core::dumpData($fixtureDir, $individualFiles);

        $this->_print('Successfully dumped current database contents into fixture directory.', array('color' => 'green'));
        $this->_print('Destination Directory: ' . $fixtureDir);
    }

    public function generarModelo()
    {
        $doctrine = $this->_getDoctrineRegistry();
        $this->_loadDoctrineModels();

        $import = $this->_createImportSchema();
        $import->setOptions($doctrine->getGenerateModelOptions());
        $import->importSchema($this->_getYamlDirectoryPath(), 'yml', $doctrine->getModelPath());
    }

    protected function _createImportSchema()
    {
        $manager = Doctrine_Manager::getInstance();
        $modelLoading = $manager->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING);

        $listener = false;
        $zendStyles = array(ZendR_Doctrine_Core::MODEL_LOADING_ZEND);
        if (!in_array($modelLoading, $zendStyles)) {
            $import = new Doctrine_Import_Schema();
        } else {
            $response = $this->_registry->getResponse();
            $listener = new ZendR_Doctrine_Import_Listener_Response($response);
            $import = new ZendR_Doctrine_Import_Schema();
            $import->setListener($listener);
        }
        return $import;
    }

    private function _generateYamlFromModels()
    {
        $this->_loadDoctrineModels();

        $yamlDir = $this->_getYamlDirectoryPath();
        Doctrine_Core::generateYamlFromModels($yamlDir);

        $this->_print('Successfully generated yaml schema files from model.', array('color' => 'green'));
        $this->_print('Destination Directory: ' . $yamlDir);
    }

    public function generarEsquemaDesdeBd()
    {
        $this->_initDoctrineResource();

        $yamlDir = $this->_getYamlDirectoryPath();
        Doctrine_Core::generateYamlFromDb($yamlDir);

        $this->_print('Succsesfully generated yaml schema files from database.', array('color' => 'green'));
        $this->_print('Destination Directory: ' . $yamlDir);
    }

	public function generarCambiosDelModelo()
    {
		$this->_initDoctrineResource();
		$migrationsPath = $this->_getMigrationsDirectoryPath();
		$yamlSchemaPath = $this->_getYamlDirectoryPath();

		$changes = Doctrine_Core::generateMigrationsFromDiff($migrationsPath, $yamlSchemaPath . '-old', $yamlSchemaPath);

		$numChanges = count($changes, true) - count($changes);
		$result = ($result1 || $numChanges) ? true:false;

		if ($result) {
			$this->_print('Generated migration classes from the database successfully.');
		} else {
			throw new Exception('Could not generate migration classes from database');
		}
    }

	public function ejecutarCambiosDelModelo($toVersion = null)
    {
        $this->_initDoctrineResource();

        $currentVersion = $this->getCurrentMigrationVersion();

        $migratePath = $this->_getMigrationsDirectoryPath();
        $newVersion = Doctrine_Core::migrate($migratePath, $toVersion);

        $this->generarModelo();
        $this->_print('Migrated from version ' . $currentVersion . ' to ' . $newVersion);
    }

    private function _showMigration()
    {
        $this->_initDoctrineResource();

        $this->_print('The current migration version is: ' . $this->getCurrentMigrationVersion());
    }

    private function _show()
    {
        $this->_loadDoctrineModels();

        $modules = ZendR_Doctrine_Core::getAllModelDirectories();

        $this->_print('Current Doctrine Model Directories:');
        foreach ($modules AS $module => $directory) {
            $this->_print(' * Module ' . $module . ': ' . $directory);
        }
        $this->_registry->getResponse()->appendContent('');

        $models = ZendR_Doctrine_Core::getLoadedModels();
        $this->_print('All current models:');
        foreach ($models AS $class) {
            $this->_print(' * ' . $class);
        }

        $this->_registry->getResponse()->appendContent('');

		$this->showMigration();
    }

    public function generarCrud($classModel, $force = null, $module = null)
    {
        $this->_initDoctrineResource();
        $modelsLoad = $this->_loadDoctrineModels();
        $models = array();
        foreach ($modelsLoad as $model => $modelPath) {
            $models[] = $model;
        }

        try {
            $models = ZendR_Doctrine_Core::filterInvalidModels($models);

            if (trim($classModel) == '*') {
                foreach ($models as $model) {
                    echo "[CRUD] generation for '" . $model . "'\n";
                    ZendR_Doctrine_Core::generateCrudFromModel($model, $module, $force);
                }
            } else {
                $classModels = explode(',', $classModel);
                foreach ($classModels as $model) {
                    $model = trim($model);
                    if (!in_array($model, $models)) {
                        throw new Exception("No exists model '" . $model . "'.");
                    }

                    echo "[CRUD] generation for '" . $model . "'\n";
                    ZendR_Doctrine_Core::generateCrudFromModel($model, $module, $force);
                }
            }
        } catch(Exception $e) {
            $this->_print("Error: " . $e->getMessage());
        }
    }

    public function generarFormulario($classModel, $force = null, $module = null)
    {
        $this->_initDoctrineResource();
        $modelsLoad = $this->_loadDoctrineModels();
        $models = array();
        foreach ($modelsLoad as $model => $modelPath) {
            $models[] = $model;
        }

        try {
            $models = ZendR_Doctrine_Core::filterInvalidModels($models);

            $classModels = explode(',', $classModel);
            foreach ($classModels as $model) {
                $model = trim($model);
                if (!in_array($model, $models)) {
                    throw new Exception("No exists model '" . $model . "'.");
                }

                ZendR_Doctrine_Core::generateFormFromModel($model, $module, $force);
                echo "[FORM] generation for '" . $model . "'\n";
            }
        } catch(Exception $e) {
            $this->_print("Error: " . $e->getMessage());
        }
    }

	protected function getCurrentMigrationVersion()
    {
        $migratePath = $this->_getMigrationsDirectoryPath();
        $migration = new Doctrine_Migration($migratePath);
        return $migration->getCurrentVersion();
    }

    protected function _loadDoctrineModels()
    {
        $this->_initDoctrineResource();
        $manager = Doctrine_Manager::getInstance();

        return ZendR_Doctrine_Core::loadModels(
            $this->_getDoctrineRegistry()->getModelPath(),
            $manager->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING)
        );
    }

    protected function _getYamlDirectoryPath()
    {
        $yamlDir = $this->_loadProfileRequired()->search('YamlSchemaDirectory');
        if (!$yamlDir) {
            throw new Exception("No YAML Schema Directory path is configured in your ZF project.");
        }
        return $yamlDir->getPath();
    }

    protected function _getDataFixtureDirectoryPath()
    {
        $fixtureDir = $this->_loadProfileRequired()->search('DataFixturesDirectory');
        if (!$fixtureDir) {
            throw new Exception('No data fixture directory is configured in your ZF project.');
        }
        return $fixtureDir->getPath();
    }

    protected function _getMigrationsDirectoryPath()
    {
        $migrateDir = $this->_loadProfileRequired()->search('MigrationsDirectory');
        if (!$migrateDir) {
            throw new Exception('No migrations directory is configured in your ZF project.');
        }
        return $migrateDir->getPath();
    }

    protected function _getSqlDirectoryPath()
    {
        $sqlDir = $this->_loadProfileRequired()->search('SqlDirectory');
        if (!$sqlDir) {
            throw new Exception('No sql directory is configured in your ZF project.');
        }
        return $sqlDir->getPath();
    }

    protected function _initDoctrineResource()
    {
        if($this->_doctrineRegistry != null) {
            return;
        }

        $profile = $this->_loadProfileRequired();

        /* @var $app Zend_Application */
        $app = $profile->search('BootstrapFile')->getApplicationInstance();
        $app->bootstrap();

        $container = $app->getBootstrap()->getContainer();
        if (!isset($container->doctrine)) {
            throw new ZendR_Doctrine_Exception('There is no "doctrine" resource enabled in the current project.');
        }

        $this->_doctrineRegistry = $container->doctrine;
    }

    /**
     * @return ZendR_Doctrine_Registry
     */
    protected function _getDoctrineRegistry()
    {
        if ($this->_doctrineRegistry == null) {
            $this->_initDoctrineResource();
        }
        return $this->_doctrineRegistry;
    }

    /**
     * @param string $line
     * @param array $decoratorOptions
     */
    protected function _print($line, array $decoratorOptions = array())
    {
        $this->_registry->getResponse()->appendContent("[Doctrine] " . $line, $decoratorOptions);
    }

    /**
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     */
    public function colocarBaseUrl($action, $controller, $module = 'default', $prefijo = 'phtml')
    {
        $profile = $this->_loadProfileRequired();
        /* @var $app Zend_Application */
        $app = $profile->search('BootstrapFile')->getApplicationInstance();
        $options = $app->getBootstrap()->getOptions();

        if (isset($options['resources']['frontController']['moduleDirectory'])) {
            $moduleDirectory = ltrim($options['resources']['frontController']['moduleDirectory'], '/\\');
            $moduleDir = $moduleDirectory . '/' . $module;
            if (!is_dir($moduleDir)) {
                throw new Exception('Module Directory: "' . $moduleDir . '" not found');
            }

            $viewDir = $moduleDir . '/views/scripts/' . $controller;
            if (!is_dir($viewDir)) {
                throw new Exception('View Scripts Directory: "' . $viewDir . '" not found');
            }
            
            $file = $viewDir . '/' . $action . '.' . $prefijo;
            if (!is_file($file)) {
                throw new Exception('File: "' . $file . '" not found');
            }
        } elseif (isset($options['resources']['frontController']['controllerDirectory'])) {
            $controllerDirectory = ltrim($options['resources']['frontController']['controllerDirectory'], '/\\');
            if (!is_dir($controllerDirectory)) {
                throw new Exception('Controller Directory: "' . $controllerDirectory . '" not found');
            }

            $parentControllerDir = dir($controllerDirectory);
            $viewDir = $parentControllerDir . '/views/scripts/' . $controller;
            if (!is_dir($moduleDir)) {
                throw new Exception('View Scripts Directory: "' . $viewDir . '" not found');
            }

            $file = $viewDir . '/' . $action . '.' . $prefijo;
            if (!is_file($file)) {
                throw new Exception('File: "' . $file . '" not found');
            }
        } else {
            throw new Exception('Controller and Module directory not found');
        }

        ZendR_Inflector::colocarBaseUrl($file);
        echo '[BaseUrl] Success';
    }

    /**
     *
     * @param string $layout
     */
    public function colocarBaseUrlLayout($layout, $prefijo = 'phtml')
    {
        $profile = $this->_loadProfileRequired();
        /* @var $app Zend_Application */
        $app = $profile->search('BootstrapFile')->getApplicationInstance();
        $options = $app->getBootstrap()->getOptions();
        
        if (!isset($options['resources']['layout'])) {
            throw new Exception('Resource Layout not found');
        }

        if (!isset($options['resources']['layout']['layoutPath'])) {
            throw new Exception('Resource Layout Path not found');
        }

        $layoutPath = ltrim($options['resources']['layout']['layoutPath'], '/\\');
        if (!is_dir($layoutPath)) {
            throw new Exception('Layout Path: "' . $layoutPath . '" not found');
        }

        $file = $layoutPath . '/' . $layout . '.' . $prefijo;
        ZendR_Inflector::colocarBaseUrl($file);
        echo '[BaseUrl] Success';
    }

    public function generarProyecto()
    {

    }

    public function generarAutenticacion()
    {

    }

    public function generarAutorizacion()
    {

    }
}
