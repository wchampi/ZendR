<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Crud_Schema
{
	/**
	 *
	 * @var string
	 */
    private $_classModel = null;

	/**
	 *
	 * @var string
	 */
    private $_modulePath = null;
	/**
	 *
	 * @var string
	 */
    private $_moduleName = null;
    /**
	 *
	 * @var string
	 */
    private $_force = null;

	/**
	 *
	 * @var Doctrine_Table
	 */
    private $_table = null;
	
	public function __construct($classModel, $moduleName, $modulePath, $force)
    {
		$this->_classModel = $classModel;
        $this->_moduleName = $moduleName;
        $this->_modulePath = $modulePath;
        $this->_force = $force;
	}

	/**
     *
     * @return Doctrine_Table
     */
    public function getTable()
    {
        if ($this->_table == null) {
            $this->setTable();
        }
		
        return $this->_table;
    }

    public function setTable()
    {
        $this->_table = Doctrine::getTable($this->_classModel);
    }

	public function getClassModel()
    {
        return $this->_classModel;
    }

    public function getModuleName()
    {
        return $this->_moduleName;
    }

    public function getForce()
    {
        return $this->_force;
    }

    public function getControllerName()
    {
        return strtolower(substr($this->_classModel, 0, 1)) . substr($this->_classModel, 1);
    }

    public function getControllerNameIze()
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '-$1', $this->_classModel));
    }

    public function getModelNameIze($model)
    {
        return preg_replace('~(?<=\\w)([A-Z])~', ' $1', $model);
    }

    public function getUrlController()
    {
        $module = $this->getModuleName() == 'default' ? '' : $this->getModuleName() . '/';
        return $module . $this->getControllerNameIze() . '/';
    }

	public function getClassNameController()
    {
        $moduleName = $this->_moduleName == 'default' ? '' : ucfirst($this->_moduleName) . '_';
        return $moduleName . $this->_classModel . 'Controller';
    }

	public function getDirectoryPathController()
	{
		return $this->_modulePath . DIRECTORY_SEPARATOR . 'controllers';
	}
	
	public function getFilePathController()
    {
        return $this->getDirectoryPathController() . DIRECTORY_SEPARATOR . $this->_classModel . 'Controller.php';
    }

	public function getClassNameForm()
    {
        $moduleName = $this->_moduleName == 'default' ? '' : ucfirst($this->_moduleName) . '_';
        return $moduleName . 'Forms_' . $this->_classModel;
    }

    public function getClassNameFormBusqueda()
    {
        return $this->getClassNameForm() . 'Search';
    }

	public function getDirectoryPathForm()
	{
		return $this->_modulePath . DIRECTORY_SEPARATOR . 'forms';
	}
	
	public function getFilePathFormBusqueda()
	{
		return $this->getDirectoryPathForm() . DIRECTORY_SEPARATOR . $this->_classModel . 'Search.php';
	}

	public function getFilePathForm()
	{
		return $this->getDirectoryPathForm() . DIRECTORY_SEPARATOR . $this->_classModel . '.php';
	}

	public function getDirectoryPathView()
	{
		return $this->_modulePath . DIRECTORY_SEPARATOR . 'views'
			. DIRECTORY_SEPARATOR . 'scripts'. DIRECTORY_SEPARATOR . $this->getControllerNameIze();
	}

    public function getFilePathViewVer()
	{
		return $this->getDirectoryPathView() . DIRECTORY_SEPARATOR . Zend_Registry::get('Zend_Translate')->_('view') . '.phtml';
	}

	public function getFilePathViewForm()
	{
		return $this->getDirectoryPathView() . DIRECTORY_SEPARATOR . 'form.phtml';
	}

	public function getFilePathViewListar()
	{
		return $this->getDirectoryPathView() . DIRECTORY_SEPARATOR . Zend_Registry::get('Zend_Translate')->_('list') . '.phtml';
	}
    
	public function getDirectoryPathTable()
	{
        $classModel = $this->_classModel;
		$modelFiles = ZendR_Doctrine_Core::loadAllZendModels();
		$modelPath = dirname($modelFiles->$classModel);

		return $modelPath . '/table/';
	}
    
	public function getFilePathTable()
	{
		return $this->getDirectoryPathTable() . $this->_classModel . 'Table.php';
	}

	public function getBaseDirectoryName()
	{
		$direc = explode(DIRECTORY_SEPARATOR, $this->_modulePath);
		return end(explode('/', end($direc)));
	}

	public function getBaseRequire()
	{
		return ($this->getBaseDirectoryName() == 'application') ? '' : $this->getBaseDirectoryName() . '/';
	}

	public function getLabelAtrib($column)
    {
        return ucwords(str_replace('_', ' ', $column));
    }

    public function getNameFunction($column)
    {
        return str_replace(' ', '', $this->getLabelAtrib($column));
    }

    public function getNameFunctionGet($column)
    {
        return 'get' . $this->getNameFunction($column);
    }

    public function getNameFunctionSet($column)
    {
        return 'set' . $this->getNameFunction($column);
    }

    public function getColumnsList($model = null)
    {
		if ($model == null) {
			$table = $this->getTable();
		} else {
			$table = Doctrine::getTable($model);
		}

        $columns = array();
        $numberColumns = 1;
        $relations = $table->getRelations();
        foreach ($table->getColumns() as $column => $property) {
            $isRelation = false;
            foreach ($relations as $relation) {
                if ($column == $relation->getLocalFieldName() && $relation->getType() == Doctrine_Relation::ONE) {
                    $isRelation = true;
                    break;
                }
            }
            
            $isPassword = false;
            if (strpos($column, 'clave') !== false) {
                $isPassword = true;
            }
            if (strpos($column, 'password') !== false) {
                $isPassword = true;
            }

            $isPrimary = false;
            if (isset ($property['primary'])) {
                if ($property['primary'] == 'true') {
                    $isPrimary = true;
                }
            }
            
            if (!$isRelation && !$isPassword && $property['type'] != 'blob' 
                && $property['type'] != 'clob' && $numberColumns <= 9 && !$isPrimary) {
                $columns[$column] = $property;
                $numberColumns++;
            }
        }
        
        return $columns;
    }

	public function getColumnsSearch()
	{
		$columns = array();
		foreach ($this->getColumnsList() as $column => $property) {
            $isPrimary = false;
            if (isset ($property['primary'])) {
                if ($property['primary'] == 'true') {
                    $isPrimary = true;
                }
            }

            $isFecha = false;
            if ($property['type'] == 'date' && $property['type'] == 'timestamp') {
                $isFecha = true;
            }

            $isImage = false;
            if (strpos($column, 'image') !== false || strpos($column, 'foto') !== false
                || strpos($column, 'icono') !== false || strpos($column, 'logo') !== false) {
                $isImage = true;
            }

            if (!$isPrimary && !$isFecha && !$isImage) {
                $columns[$column] = $property;
            }
		}
		
        return $columns;
	}

	public function getColumnsForm()
	{
		$columns = array();
		foreach ($this->getTable()->getColumns() as $column => $property) {
            $isPrimary = false;
            if (isset ($property['primary'])) {
                if ($property['primary'] == 'true') {
                    $isPrimary = true;
                }
            }
			if (!$isPrimary) {
				$columns[$column] = $property;
			}
		}

        return $columns;
	}

    public function getUrlVer($c = '')
    {
        return $this->getUrlController()
            . Zend_Registry::get('Zend_Translate')->_('view') . '/id/\' . $' . trim($c) . $this->getControllerName() . '->'
            . $this->getNameFunctionGet($this->getTable()->getIdentifier()) . '()';
    }

    public function getUrlListar()
    {
        return $this->getUrlController() . Zend_Registry::get('Zend_Translate')->_('list');
    }

    public function getUrlEditar($c = '')
    {
        return $this->getUrlController()
            . Zend_Registry::get('Zend_Translate')->_('edit') . '/id/\' . $' . trim($c) . $this->getControllerName() . '->'
            . $this->getNameFunctionGet($this->getTable()->getIdentifier()) . '()';
    }

    public function getUrlAgregar()
    {
        return $this->getUrlController() . Zend_Registry::get('Zend_Translate')->_('add');
    }

    public function getUrlEliminar($c = '')
    {
        return $this->getUrlController()
            . Zend_Registry::get('Zend_Translate')->_('delete') . '/id/\' . $' . trim($c) . $this->getControllerName() . '->'
            . $this->getNameFunctionGet($this->getTable()->getIdentifier()) . '()';
    }

    public function obtenerColumnDescription($model = null)
    {
        $columnDescription = '';
		foreach ($this->getColumnsList($model) as $columnDescription => $property) {
            $isPrimary = false;
            if (isset ($property['primary'])) {
                if ($property['primary'] == 'true') {
                    $isPrimary = true;
                }
            }
			if (!$isPrimary) {
				break;
			}
		}
        return $columnDescription;
    }

    public function obtenerColumnId($model = null)
    {
        if ($model == null) {
			$table = $this->getTable();
		} else {
			$table = Doctrine::getTable($model);
		}
        
        $columnId = '';
		foreach ($table->getColumns() as $column => $property) {
            $isPrimary = false;
            if (isset ($property['primary'])) {
                if ($property['primary'] == 'true') {
                    $columnId = $column;
                    break;
                }
            }
		}
        return $columnId;
    }
    
	public function builder()
	{
        $primaryColumn = $this->getTable()->getIdentifier();
        if (is_array($primaryColumn)) {
            throw new Exception('The Model should have "only one primary key"');
        }
        
        $builderController  = new ZendR_Doctrine_Import_Crud_BuilderController($this);
		$builderController->build();

        $builderForm = new ZendR_Doctrine_Import_Crud_BuilderForm($this);
		$builderForm->build();

        $builderView = new ZendR_Doctrine_Import_Crud_BuilderView($this);
		$builderView->build();

		$builderTable = new ZendR_Doctrine_Import_Crud_BuilderTable($this);
		$builderTable->build();
	}
}