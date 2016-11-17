<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Crud_BuilderForm
{
	/**
	 *
	 * @var ZendR_Doctrine_Import_Crud_Schema
	 */
	private $_schema = null;

    public function __construct($schema)
    {
		$this->_schema = $schema;
    }

    public function getMethodInitFormBusqueda()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName('init');

		$bodyColumns = '$campo = new Zend_Form_Element_Select(\'' . Zend_Registry::get('Zend_Translate')->_('campo') . '\');' . "\n"
            . '$campo->setDecorators(ZendR_Decorators::factory(\'fieldInline\'))' . "\n";

        foreach ($this->_schema->getColumnsSearch() as $column => $property) {
            if ($property['type'] != 'date' && $property['type'] != 'timestamp') {
                $bodyColumns .= "\t" . '->addMultiOption(\'' . $column . '\', \'' . ZendR_Inflector::label($column) . '\')' . "\n";
            }
		}

        $bodyColumns .= "\t" . '->setLabel(\'' . Zend_Registry::get('Zend_Translate')->_('Search by') . ': \');' . "\n"
            . '$this->addElement($campo);' . "\n"
            . "\n"
            . '$filtro = $this->createElement(\'text\', \'filtro\')' . "\n"
            . "\t" . '->setDecorators(ZendR_Decorators::factory(\'fieldInline\'))' . "\n"
            . "\t" . '->addFilter(\'StripTags\')' . "\n"
            . "\t" . '->addFilter(\'StringTrim\');' . "\n"
            . '$this->addElement($filtro);' . "\n\n"
        ;

		$body = '$this->setMethod(\'post\');' . "\n"
			. '$this->setAttrib(\'name\', \'frm' . $this->_schema->getClassModel() . 'Search\');' . "\n"
            . "\n"
			. $bodyColumns
			. $this->renderButtonSearch()
		;

        $method->setBody($body);

        return $method;
    }

	public function getMethodInitForm()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName('init');

		$bodyColumns = '';
		foreach ($this->_schema->getColumnsForm() as $column => $property) {
			$bodyColumns .= $this->renderElement($column, $property);
		}

        $bodyManyToMany = '';
        foreach ($this->_schema->getTable()->getRelations() as $relation) {
            if ($relation instanceof Doctrine_Relation_Association) {
                $bodyManyToMany .= $this->renderElementManyToMany($relation);
            }
		}

		$body = '$this->setAttrib(\'name\', \'frm' . $this->_schema->getClassModel() . '\');' . "\n"
            . "\n"
			. $bodyColumns
			. $this->renderGroup($this->_schema->getColumnsForm(), 'gDatos')
			. "\n"
            . $bodyManyToMany
            . "\n"
		;

        $method->setBody($body);

        return $method;
    }
    
	public function buildFormBusqueda()
	{
		$class = new Zend_CodeGenerator_Php_Class();
        $class->setName($this->_schema->getClassNameFormBusqueda())
            ->setExtendedClass('ZendR_Form_Search');
			
        $class->setMethods(array(
            $this->getMethodInitFormBusqueda(),
        ));

        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        if (!is_file($this->_schema->getFilePathFormBusqueda()) || $this->_schema->getForce() == 'force') {
            file_put_contents($this->_schema->getFilePathFormBusqueda(), $file->generate());
            echo "[OK] search form has been created\n";
        } else {
            echo "[Warning] search form has not been created, already exists\n";
        }
	}

	public function buildForm()
	{
		$class = new Zend_CodeGenerator_Php_Class();
        $class->setName($this->_schema->getClassNameForm())
            ->setExtendedClass('ZendR_Form_Crud');

        $class->setMethods(array(
            $this->getMethodInitForm(),
        ));

        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        if (!is_file($this->_schema->getFilePathForm()) || $this->_schema->getForce() == 'force') {
            file_put_contents($this->_schema->getFilePathForm(), $file->generate());
            echo "[OK] form has been created\n";
        } else {
            echo "[Warning] form has not been created, already exists\n";
        }
	}

    public function build()
    {
		if (!is_dir($this->_schema->getDirectoryPathForm())) {
			mkdir($this->_schema->getDirectoryPathForm());
		}

		$this->buildForm();
		$this->buildFormBusqueda();
    }

	public function renderButtonDelete()
	{
		return '$eliminar = $this->createElement(\'button\', \'' . Zend_Registry::get('Zend_Translate')->_('delete') . '\')' . "\n"
			. "\t" . '->setLabel(\'' . Zend_Registry::get('Zend_Translate')->_('Delete') . '\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'buttomRight\'))' . "\n"
            . "\t" . '->setAttrib(\'class\', \'btnActionSupr\');' . "\n"
			. '$this->addElement($eliminar);';
	}

	public function renderButtonAccept()
	{
		return '$aceptar = $this->createElement(\'submit\', \'' . Zend_Registry::get('Zend_Translate')->_('accept') . '\')' . "\n"
			. "\t" . '->setLabel(\'' . Zend_Registry::get('Zend_Translate')->_('Accept') . '\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'buttomLeft\'))' . "\n"
            . "\t" . '->setAttrib(\'class\', \'btnAction\');' . "\n"
			. '$this->addElement($aceptar);';
	}

	public function renderButtonSearch()
	{
		return '$buscar = $this->createElement(\'submit\', \'' . Zend_Registry::get('Zend_Translate')->_('search') . '\')' . "\n"
			. "\t" . '->setLabel(\'' . Zend_Registry::get('Zend_Translate')->_('Search') . '\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'buttom\'))' . "\n"
            . "\t" . '->setAttrib(\'class\', \'btnAction\');' . "\n"
			. '$this->addElement($buscar);';
	}

	public function renderElement($column, $property)
	{
		$isRelation = false;
		foreach ($this->_schema->getTable()->getRelations() as $relation) {
			if ($column == $relation->getLocalFieldName() && $relation instanceof Doctrine_Relation_LocalKey) {
				$isRelation = true;
				break;
			}
		}

		$element = '';
		if ($isRelation) {
			$element = $this->renderElementForeignKey($column, $property, $relation);
		} else {
			switch ($property['type']) {
				case 'string':
					$element = $this->renderElementString($column, $property);
					break;
				case 'date':
					$element = $this->renderElementDate($column, $property);
					break;
				case 'integer':
					$element = $this->renderElementInteger($column, $property);
					break;
				case 'float':
					$element = $this->renderElementFloat($column, $property);
					break;
				case 'decimal':
					$element = $this->renderElementFloat($column, $property);
					break;
				case 'boolean':
					$element = $this->renderElementBoolean($column, $property);
					break;
				case 'blob':
					$element = $this->renderElementTextArea($column, $property);
					break;
				case 'clob':
					$element = $this->renderElementTextArea($column, $property);
					break;
                case 'enum':
					$element = $this->renderElementEnum($column, $property);
					break;
                case 'timestamp':
					$element = $this->renderElementTimestamp($column, $property);
					break;
                case 'time':
					$element = $this->renderElementTime($column, $property);
					break;
			}
		}
		return $element;
	}

	public function renderElementString($column, $property, $decorator = 'field')
	{
        $atribs = '';
        $labelRequired = '';
        if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
        }
        
        if (strpos($column, 'image') !== false || strpos($column, 'foto') !== false 
            || strpos($column, 'icono') !== false || strpos($column, 'logo') !== false) {
            $uploadPath = UPLOAD_PATH;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath);
            }

            $uploadImagePath = $uploadPath . '/' . $this->_schema->getControllerNameIze();
            if (!is_dir($uploadImagePath)) {
                mkdir($uploadImagePath);
            }

             return '$' . ZendR_Inflector::variable($column) . ' = new ZendR_Form_Element_FileImage(\'' . $column . '\');' . "\n"
                . '$' . ZendR_Inflector::variable($column) . '->setLabel(\'' . ZendR_Inflector::label($column) . ':\')' . "\n"
                . "\t" .  '->addValidator(\'Extension\', false, \'jpg,jpeg,png,gif\')' . "\n"
                . "\t" .  '->setDescription(\'jpg,jpeg,png,gif\')' . "\n"
                . "\t" .  '->setDestination(UPLOAD_PATH . \'/' . $this->_schema->getControllerNameIze() . '\')' . "\n"
                . "\t" .  '->setDecorators(ZendR_Decorators::factory(\'fieldFile\'));' . "\n"
                . '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
        } elseif (strpos($column, 'telefono') !== false || strpos($column, 'phone') !== false) {
            $numberLength = 7;
            if (strpos($column, 'cel') !== false || strpos($column, 'movil') !== false) {
                $numberLength = 9;
            }
            return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'text\', \'' . $column . '\')' . "\n"
                . "\t" . '->setLabel(\'' . ZendR_Inflector::label($column)
                . ' ' . $labelRequired . ' :\')' . "\n"
                . $atribs
                . "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'))' . "\n"
                . "\t" . '->addValidator(\'StringLength\', false, array(' . $numberLength . ', 13))' . "\n"
                . "\t" . '->addValidator(\'digits\')' . "\n"
                . "\t" . '->setAttrib(\'size\', 13)' . "\n"
                . "\t" . '->setAttrib(\'maxlength\', 13)' . "\n"
                . "\t" . '->setDescription(\'' . Zend_Registry::get('Zend_Translate')->_('Enter') . ' ' . Zend_Registry::get('Zend_Translate')->_('of') . ' ' . $numberLength . ' ' . Zend_Registry::get('Zend_Translate')->_('to') . ' 13 ' . Zend_Registry::get('Zend_Translate')->_('Digits') . '\')' . "\n"
                . "\t" . '->addFilter(\'StringTrim\');' . "\n"
                . '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
        } elseif ($column == 'dni') {
            return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'text\', \'' . $column . '\')' . "\n"
                . "\t" . '->setLabel(\'' . ZendR_Inflector::label($column)
                . ' ' . $labelRequired . ' :\')' . "\n"
                . $atribs
                . "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'))' . "\n"
                . "\t" . '->addValidator(\'StringLength\', false, array(8, 8))' . "\n"
                . "\t" . '->addValidator(\'digits\')' . "\n"
                . "\t" . '->setAttrib(\'size\', 8)' . "\n"
                . "\t" . '->setAttrib(\'maxlength\', 8)' . "\n"
                . "\t" . '->setDescription(\'' . Zend_Registry::get('Zend_Translate')->_('Enter') . ' 8 ' . Zend_Registry::get('Zend_Translate')->_('Digits') . '\');' . "\n"
                . '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
        } else {
            $validators = '';
            if (strpos($column, 'correo') !== false || strpos($column, 'mail') !== false) {
                $validators .= "\t" . '->addValidator(\'EmailAddress\')' . "\n";
            }

            $atribs .= "\t" . '->setAttrib(\'size\', ' . (int)($property['length'] / 2) . ')' . "\n";
            $type = 'text';
            if (strpos($column, 'password') !== false || strpos($column, 'clave') !== false) {
                $validators .= "\t" . '->addValidator(\'StringLength\', false, array(5, 15))' . "\n";
                $atribs .= "\t" . '->setAttrib(\'maxlength\', ' . 15 . ')' . "\n";
                $type = 'password';
            } else {
                $validators .= "\t" . '->addValidator(\'StringLength\', false, array(0, ' . $property['length'] . '))' . "\n";
                $atribs .= "\t" . '->setAttrib(\'maxlength\', ' . $property['length'] . ')' . "\n";
            }

            return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'' . $type . '\', \'' . $column . '\')' . "\n"
                . "\t" . '->setLabel(\'' . ZendR_Inflector::label($column) . ' ' . $labelRequired . ' :\')' . "\n"
                . "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'))' . "\n"
                . $validators
                . $atribs
                . "\t" . '->setDescription(\'Max. ' . $property['length'] . ' ' . Zend_Registry::get('Zend_Translate')->_('characters') . ' \')' . "\n"
                . "\t" . '->addFilter(\'StripTags\')' . "\n"
                . "\t" . '->addFilter(\'StringTrim\');' . "\n"
                . '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
        }
	}

	public function renderElementInteger($column, $property, $decorator = 'field')
	{
		$validators = "\t" . '->addValidator(\'Int\')' . "\n";

		$atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}
		
		return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'text\', \'' . $column . '\')' . "\n"
			. "\t" . '->setLabel(\'' . ZendR_Inflector::label($column) . ' ' . $labelRequired . ' :\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'))' . "\n"
			. $validators
			. $atribs
			. "\t" . '->addFilter(\'StringTrim\');' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
	}

	public function renderElementFloat($column, $property, $decorator = 'field')
	{
		$validators = "\t" . '->addValidator(\'Float\')' . "\n";

		$atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}

		return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'text\', \'' . $column . '\')' . "\n"
			. "\t" . '->setLabel(\'' . ZendR_Inflector::label($column) . ' ' . $labelRequired . ' :\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'))' . "\n"
			. $validators
			. $atribs
			. "\t" . '->addFilter(\'StringTrim\');' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
	}

	public function renderElementDate($column, $property, $decorator = 'field')
	{
		$atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}
		
		return '$' . ZendR_Inflector::variable($column) . ' = new ZendR_Form_Element_Date(\'' . $column . '\');' . "\n"
			. '$' . ZendR_Inflector::variable($column) . '->setLabel(\'' . ZendR_Inflector::label($column)
			. ' ' . $labelRequired . ' :\')' . "\n"
			. $atribs
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'));' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
	}
	
	public function renderElementBoolean($column, $property, $decorator = 'field')
	{
		return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'checkbox\', \'' . $column . '\')' . "\n"
			. "\t" . '->setLabel(\'' . ZendR_Inflector::label($column) . ' :\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'));' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
	}

	public function renderElementTextArea($column, $property, $decorator = 'field')
	{
		$atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}

        $validators = '';
        $descriptions = '';
        if (isset($property['length'])) {
            if ((int)$property['length'] > 0) {
                $validators = "\t" . '->addValidator(\'StringLength\', false, array(0, ' . $property['length'] . '))' . "\n";
                $descriptions = "\t" . '->setDescription(\'Max. ' . $property['length'] . ' ' . Zend_Registry::get('Zend_Translate')->_('characters') . ' \')' . "\n";
            }
        }
		
		return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'textarea\', \'' . $column . '\')' . "\n"
			. "\t" . '->setLabel(\'' . ZendR_Inflector::label($column) . ' ' . $labelRequired . ' :\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'))' . "\n"
			. $atribs
            . $validators
            . $descriptions
			. "\t" . '->setAttrib(\'rows\', 4)' . "\n"
			. "\t" . '->setAttrib(\'cols\', 75)' . "\n"
			. "\t" . '->addFilter(\'StripTags\')' . "\n"
			. "\t" . '->addFilter(\'StringTrim\');' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
	}

    public function renderElementEnum($column, $property, $decorator = 'field')
	{
		$atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}

        $listValues = '';
        foreach($property['values'] as $vl){
            $listValues .= "\t" .'->addMultiOption("'.$vl.'","'.$vl.'")' . "\n";
        }


		return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'select\', \'' . $column . '\')' . "\n"
			. "\t" . '->setLabel(\'' .ZendR_Inflector::label($column) . ' ' . $labelRequired . ' :\')' . "\n"
            . "\t" . '->addMultiOption(\'\', \'Selecione...\')' . "\n"
            . $listValues
			. $atribs
            . "\t" . '->addFilter(\'StripTags\')' . "\n"
            . "\t" . '->addFilter(\'StringTrim\')' . "\n"
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'));' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n"
			. "\n";

	}

    public function renderElementTimestamp($column, $property, $decorator = 'field')
	{
        $atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}

		return '$' . ZendR_Inflector::variable($column) . ' = new ZendR_Form_Element_Timestamp(\'' . $column . '\');' . "\n"
			. '$' . ZendR_Inflector::variable($column) . '->setLabel(\'' . ZendR_Inflector::label($column)
			. ' ' . $labelRequired . ' :\')' . "\n"
			. $atribs
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'));' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
    }

    public function renderElementTime($column, $property, $decorator = 'field')
	{
        $atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}

		return '$' . ZendR_Inflector::variable($column) . ' = new ZendR_Form_Element_Time(\'' . $column . '\');' . "\n"
			. '$' . ZendR_Inflector::variable($column) . '->setLabel(\'' . ZendR_Inflector::label($column)
			. ' ' . $labelRequired . ' :\')' . "\n"
			. $atribs
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'));' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n\n";
    }

	public function renderElementForeignKey($column, $property, $relation, $decorator = 'field')
	{
		$validators = '';
		if ($property['type'] == 'integer') {
			$validators .= "\t" . '->addValidator(\'Int\')' . "\n";
		}

		$atribs = '';
		$labelRequired = '';
		if (isset($property['notnull'])) {
            if (trim($property['notnull']) == '1') {
                $labelRequired = $property['notnull'] ? '(*)' : '';
                $atribs .= "\t" . '->setRequired(true)' . "\n";
            }
		}

		$foreignColDes = $this->_schema->obtenerColumnDescription($relation->getClass());

		$varSing	= ZendR_Inflector::variable($relation->getAlias());
		$varPlu		= $varSing . 's';
		return '$' . ZendR_Inflector::variable($column) . ' = $this->createElement(\'select\', \'' . $column . '\')' . "\n"
			. "\t" . '->setLabel(\'' . $this->_schema->getModelNameIze($relation->getAlias())
            . ' ' . $labelRequired . ' :\')' . "\n"
            . "\t" . '->addMultiOption(\'\', \'' . Zend_Registry::get('Zend_Translate')->_('Select')  . '...\')' . "\n"
            . $validators
			. $atribs
			. "\t" . '->setDecorators(ZendR_Decorators::factory(\'' . $decorator . '\'));' . "\n"
			. '$this->addElement($' . ZendR_Inflector::variable($column) . ');' . "\n"
			. "\n"
			. '$' . $varPlu . ' = ' . $relation->getClass() . '::table()->findAll();' . "\n"
			. 'foreach ($' . $varPlu . ' as $' . $varSing . ') {' . "\n"
            . "\t" . '$' . ZendR_Inflector::variable($column) . '->addMultiOption($' . $varSing . '->'
			. $this->_schema->getNameFunctionGet($relation->getTable()->getIdentifier()) . '(), $'
			. $varSing . '->' . $this->_schema->getNameFunctionGet($foreignColDes) . '());' . "\n"
			. "}\n\n";
	}

    public function renderElementManyToMany(Doctrine_Relation_Association $relation)
    {
		$foreignColDes = $this->_schema->obtenerColumnDescription($relation->getClass());

		$varSing	= ZendR_Inflector::variable($relation->getClass());
		$varPlu		= $varSing . 's';
		return '$m' . $relation->getClass() . ' = new ZendR_Form_Element_Multiselect(\'' 
            . strtolower($relation->getClass()) . 's\');' . "\n"
			. '$m' . $relation->getClass() . '->setLabel(\'' . $this->_schema->getModelNameIze($relation->getAlias())
            . ' :\')' . "\n"
            . "\t" . '->setDecorators(ZendR_Decorators::factory(\'field\'));' . "\n"
			. '$this->addElement($m' . $relation->getClass() . ');' . "\n"
			. "\n"
			. '$' . $varPlu . ' = ' . $relation->getClass() . '::table()->findAll();' . "\n"
			. 'foreach ($' . $varPlu . ' as $' . $varSing . ') {' . "\n"
            . "\t" . '$m' . $relation->getClass() . '->addMultiOption($' . $varSing . '->'
			. $this->_schema->getNameFunctionGet($relation->getTable()->getIdentifier()) . '(), $'
			. $varSing . '->' . $this->_schema->getNameFunctionGet($foreignColDes) . '());' . "\n"
			. "}\n\n"
            . '$this->addDisplayGroup(' . "\n"
            . "\t" . 'array(\'' . strtolower($relation->getClass()) . 's\'),' . "\n"
            . "\t" . '\'gDatos' . $relation->getClass() . 's\',' . "\n"
            . "\t" . 'array(' . "\n"
            . "\t\t" . '\'class\' => \'fieldset\',' . "\n"
            . "\t\t" . '\'decorators\' => ZendR_Decorators::factory(\'groupFields\')' . "\n"
            . "\t" . ')' . "\n"
            . ');' . "\n";
    }

	public function renderGroup($columns, $group)
	{
		$elements = array();
		foreach ($columns as $column => $property) {
			$elements[] = '\'' . $column . '\'';
		}

		return '$this->addDisplayGroup('. "\n"
			. "\t" . 'array(' . implode(', ', $elements) . '),' . "\n"
			. "\t" . '\'' . $group . '\',' . "\n"
			. "\t" . 'array(' . "\n"
			. "\t\t" . '\'class\' => \'fieldset\',' . "\n"
			. "\t\t" . '\'decorators\' => ZendR_Decorators::factory(\'groupFields\')' . "\n"
			. "\t" . ')' . "\n"
			. ');' . "\n";
	}
	
}