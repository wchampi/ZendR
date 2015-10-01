<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Crud_BuilderController
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

    public function getMethodIndex()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName('indexAction');
        $method->setBody('$this->_redirect(\'' . $this->_schema->getUrlListar() . '\');');

        return $method;
    }

    public function getMethodListar()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName(Zend_Registry::get('Zend_Translate')->_('list') . 'Action');

        $body = '$titles = array (' . "\n";
        foreach ($this->_schema->getColumnsList() as $column => $property) {
            $body .= "\t" . '\'' . ZendR_Inflector::label($column) . '\' => \'' . $column . '\',' . "\n";
        }
        
        $body .= "\t" . '\'' . Zend_Registry::get('Zend_Translate')->_('Actions') . '\' => null,' . "\n"
            . ');' . "\n"
            . '$head = ZendR_Head::factory(' . "\n"
            . "\t" . '$titles,' . "\n"
            . "\t" . '$this->getRequest()->getParam(\'order\'),' . "\n"
            . "\t" . '$this->getRequest()->getParam(\'by\')' . "\n"
            . ');' . "\n"
            . '$this->view->head = $head;' . "\n"
            . "\n"
            . '$' . $this->_schema->getControllerName() . 's = ' . $this->_schema->getClassModel() . '::table()->filtrar($this->getRequest()->getParams(), $head->orderBy());' . "\n"
            . '$this->view->paginator = ZendR_Paginator::factory($' . $this->_schema->getControllerName() . 's, $this->getRequest()->getParam(\'' . Zend_Registry::get('Zend_Translate')->_('page') . '\'));' . "\n"
            . "\n"
            . '$form = new ' . $this->_schema->getClassNameFormBusqueda()  . '();' . "\n"
            . '$form->populate($this->getRequest()->getParams());' . "\n"
            . '$this->view->form = $form;' . "\n"
            . "\n"
            . '$this->view->title = \'' . ZendR_Inflector::plural($this->_schema->getClassModel()) . '\';'
        ;

        $method->setBody($body);

        return $method;
    }

    public function getMethodAgregar()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName(Zend_Registry::get('Zend_Translate')->_('add') . 'Action');

        $body = '$this->view->title = \'' . Zend_Registry::get('Zend_Translate')->_('Add') . ' ' . ZendR_Inflector::humanize($this->_schema->getClassModel()) . '\';' . "\n"
            . "\n"
            . '$' . $this->_schema->getControllerName() . ' = new ' . $this->_schema->getClassModel() . '();' . "\n"
            . "\n"
            . '$form = $this->_runForm($' . $this->_schema->getControllerName() . ');' . "\n"
            . "\n"
            . '$this->view->form = $form;' . "\n"
            . "\n"
            . '$this->render(\'form\');' . "\n"
        ;

        $method->setBody($body);

        return $method;
    }

    public function getMethodVer()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName(Zend_Registry::get('Zend_Translate')->_('view') . 'Action');

        $body = '$id = (int)$this->getRequest()->getParam(\'id\');' . "\n"
            . '$' . $this->_schema->getControllerName() . ' = ' . $this->_schema->getClassModel() . '::table()->find($id);' . "\n"
            . 'if (!$' . $this->_schema->getControllerName() . ') {' . "\n"
            . "\t" . '$this->_redirect(\'' . $this->_schema->getUrlListar() . '\');' . "\n"
            . '}' . "\n"
            . '$this->view->' . $this->_schema->getControllerName() . ' = $' . $this->_schema->getControllerName() . ';' . "\n"
            . "\n"
            . '$this->view->form = $this->_initForm($' . $this->_schema->getControllerName() . ');' . "\n"
            . "\n"
            . '$this->view->title = \'' . Zend_Registry::get('Zend_Translate')->_('View') . ' ' . ZendR_Inflector::humanize($this->_schema->getClassModel()) . '\';' . "\n"
        ;

        $method->setBody($body);

        return $method;
    }
    
    public function getMethodEditar()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName(Zend_Registry::get('Zend_Translate')->_('edit') . 'Action');
        
        $body = '$id = (int)$this->getRequest()->getParam(\'id\');' . "\n"
            . '$' . $this->_schema->getControllerName() . ' = ' . $this->_schema->getClassModel() . '::table()->find($id);' . "\n"
            . 'if (!$' . $this->_schema->getControllerName() . ') {' . "\n"
            . "\t" . '$this->_redirect(\'' . $this->_schema->getUrlListar() . '\');' . "\n"
            . '}' . "\n"
            . '$this->view->' . $this->_schema->getControllerName() . ' = $' . $this->_schema->getControllerName() . ';' . "\n"
            . '$this->view->title = \'' . Zend_Registry::get('Zend_Translate')->_('Edit') . ' ' . ZendR_Inflector::humanize($this->_schema->getClassModel()) . '\';' . "\n"
            . "\n"
            . '$form = $this->_runForm($' . $this->_schema->getControllerName() . ');' . "\n"
            . "\n"
            . '$this->view->form = $form;' . "\n"
            . "\n"
            . '$this->render(\'form\');' . "\n"
        ;

        $method->setBody($body);

        return $method;
    }

    public function getMethodEliminar()
    {
        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName(Zend_Registry::get('Zend_Translate')->_('delete') . 'Action');

        $body = '$id = (int)$this->getRequest()->getParam(\'id\');' . "\n"
            . '$' . $this->_schema->getControllerName() . ' = ' . $this->_schema->getClassModel()
            . '::table()->find($id);' . "\n"
            . 'if (!$' . $this->_schema->getControllerName() . ') {' . "\n"
            . "\t" . '$this->_redirect(\'' . $this->_schema->getUrlListar() . '\');' . "\n"
            . '}' . "\n"
            . "\n"
            . 'try {' . "\n"
            . "\t" . '$' . $this->_schema->getControllerName() . '->delete();' . "\n"
            . "\t" . '$this->_redirect(\'' . $this->_schema->getUrlListar() . '\');' . "\n"
            . '} catch(Exception $e) {' . "\n"
            . "\t" . '$this->view->responseError = ZendR_Error::prepareMessageModel(new '
            . $this->_schema->getClassModel() . '(), $e->getCode(), $e->getMessage());' . "\n"
            . "\t" . '$this->_forward(\'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\');' . "\n"
            . '}' . "\n"
            . "\n"
        ;

        $method->setBody($body);

        return $method;
    }

    public function getMethodRunForm()
    {
        $parameter = new Zend_CodeGenerator_Php_Parameter();
        $parameter->setName($this->_schema->getControllerName());
        $parameter->setType($this->_schema->getClassModel());

        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName('_runForm');
        $method->setVisibility('private');
        $method->setParameter($parameter);

        $bodyManyToManyGuardarDatos = '';
        foreach ($this->_schema->getTable()->getRelations() as $relation) {
            if ($relation instanceof Doctrine_Relation_Association) {
                $bodyManyToManyGuardarDatos .= $this->renderElementManyToManyGuardarDatos($relation);
            }
		}

        $body = '$form = $this->_initForm($' . $this->_schema->getControllerName() . ');' . "\n"
            . "\n"
            . 'if ($this->getRequest()->isPost()) {' . "\n"
            . "\t" . '$post = $this->getRequest()->getPost();' . "\n"
            . "\t" . 'if (!$form->isValid($post)) {' . "\n"
            . "\t\t" . '$form->populate($post);' . "\n"
            . "\t\t" . 'return $form;' . "\n"
            . "\t" . '}' . "\n"
            . "\n"
            . "\t" . 'try {' . "\n"
            . "\t\t" . '$' . $this->_schema->getControllerName() . '->merge($form->getValues());' . "\n"
            . "\t\t" . '$' . $this->_schema->getControllerName() . '->save();' . "\n"
            . $bodyManyToManyGuardarDatos
            . "\n"
            . "\t\t" . '$this->_redirect(\'' . $this->_schema->getUrlListar() . '\');' . "\n"
            . "\t" . '} catch(Exception $e) {' . "\n"
            . "\t\t" . '$form->populate($post);'. "\n"
            . "\t\t" . '$this->view->responseError = ZendR_Error::prepareMessageModel(new '
            . $this->_schema->getClassModel() . '(), $e->getCode(), $e->getMessage());' . "\n"
            . "\t" . '}' . "\n"
            . '}' . "\n"
			. "\n"
			. 'return $form;' . "\n"
        ;

        $method->setBody($body);

        return $method;
    }

    public function getMethodInitForm()
    {
        $parameter = new Zend_CodeGenerator_Php_Parameter();
        $parameter->setName($this->_schema->getControllerName());
        $parameter->setType($this->_schema->getClassModel());

        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName('_initForm');
        $method->setVisibility('private');
        $method->setParameter($parameter);

        $bodyManyToManyObtencionDatos = '';
        foreach ($this->_schema->getTable()->getRelations() as $relation) {
            if ($relation instanceof Doctrine_Relation_Association) {
                $bodyManyToManyObtencionDatos .= $this->renderElementManyToManyObtencionDatos($relation);
            }
		}

        $body = '$form = new ' . $this->_schema->getClassNameForm() . '();' . PHP_EOL
            . 'if ($this->getRequest()->getActionName() == \'' . Zend_Registry::get('Zend_Translate')->_('add') . '\') {' . PHP_EOL
            . "\t" . '$form->removeElement(\'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\');' . PHP_EOL
            . "\t" . '$form->removeElement(\'' . Zend_Registry::get('Zend_Translate')->_('delete') . '\');' . PHP_EOL
            . "\t" . '$form->removeElement(\'' . Zend_Registry::get('Zend_Translate')->_('view') . '\');' . PHP_EOL
            . '}' . PHP_EOL
            . 'if ($this->getRequest()->getActionName() == \'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\') {' . PHP_EOL
            . "\t" . '$form->populate($' . $this->_schema->getControllerName() . '->toArray());' . PHP_EOL
            . "\t" . '$form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('view') . '\')' . PHP_EOL
            . "\t\t" . '->setAttrib(\'onclick\', "self.location.href=\'"' . PHP_EOL
            . "\t\t" . '. $this->view->baseUrl(\'' . $this->_schema->getUrlVer() . ') . "\'");' . PHP_EOL
            . "\t" . '$form->removeElement(\'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\');' . PHP_EOL
            . $bodyManyToManyObtencionDatos
            . '}' . PHP_EOL
            . 'if ($this->getRequest()->getActionName() == \'' . Zend_Registry::get('Zend_Translate')->_('view') . '\') {' . PHP_EOL
            . "\t" . '$form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\')' . PHP_EOL
            . "\t\t" . '->setAttrib(\'onclick\', "self.location.href=\'"' . PHP_EOL
            . "\t\t" . '. $this->view->baseUrl(\'' . $this->_schema->getUrlEditar() . ') . "\'");' . PHP_EOL
            . "\t" . '$form->removeElement(\'' . Zend_Registry::get('Zend_Translate')->_('view') . '\');' . PHP_EOL
            . "\t" . '$form->removeElement(\'' . Zend_Registry::get('Zend_Translate')->_('accept') . '\');' . PHP_EOL
            . '}' . PHP_EOL
            . 'if ($this->getRequest()->getActionName() == \'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\' || $this->getRequest()->getActionName() == \'' . Zend_Registry::get('Zend_Translate')->_('view') . '\') {' . PHP_EOL
            . "\t" . '$form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('delete') . '\')->setAttrib(\'onclick\', "' . Zend_Registry::get('Zend_Translate')->_('delete') . '' . $this->_schema->getClassModel() . '()");' . PHP_EOL
            . '}' . PHP_EOL
            . '$form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('list') . '\')' . "\n"
            . "\t" . '->setAttrib(\'onclick\', "self.location.href=\'"' . PHP_EOL
            . "\t" . '. $this->view->baseUrl(\'' . $this->_schema->getUrlListar() . '\') . "\'");' . PHP_EOL
            . "\n"
			. 'return $form;' . "\n"
        ;

        $method->setBody($body);

        return $method;
    }

    public function renderElementManyToManyGuardarDatos(Doctrine_Relation_Association $relation)
    {
        $varSing	= ZendR_Inflector::variable($relation->getAssociationFactory()->getClassnameToReturn());

        return "\n\t\t" . '$' . $this->_schema->getControllerName() . '->get'
            . $relation->getAssociationFactory()->getClassnameToReturn() . '()->delete();' . "\n"
            . "\t\t" . 'foreach ($form->getValue(\'' . strtolower($relation->getClass()) . 's\') as $id' . $relation->getClass() . ') {' . "\n"
            . "\t\t\t" . '$' . $varSing . ' = new ' . $relation->getAssociationFactory()->getClassnameToReturn() . '();' . "\n"
            . "\t\t\t" . '$' . $varSing . '->' . $this->_schema->getNameFunctionSet($relation->getLocalColumnName())
            . '($' . $this->_schema->getControllerName() . '->'
            . $this->_schema->getNameFunctionGet($this->_schema->getTable()->getIdentifier()) . '());' . "\n"
            . "\t\t\t" . '$' . $varSing . '->' . $this->_schema->getNameFunctionSet($relation->getForeignColumnName())
            . '($id' . $relation->getClass() . ');' . "\n"
            . "\t\t\t" . '$' . $varSing . '->save();' . "\n"
            . "\t\t" . '}' . "\n";
    }

    public function renderElementManyToManyObtencionDatos($relation)
    {
        $varSing	= ZendR_Inflector::variable($relation->getClass());

        return "\n\t" . '$' . ZendR_Inflector::variable($relation->getAlias()) . ' = array();' . "\n"
            . "\t" . 'foreach ($' . $this->_schema->getControllerName() . '->get' . $relation->getAlias() . '() as $' . $varSing . ') {' . "\n"
            . "\t\t" . '$' . ZendR_Inflector::variable($relation->getAlias()) . '[] = $' . $varSing . '->'
            . $this->_schema->getNameFunctionGet($relation->getTable()->getIdentifier()) . '();' . "\n"
            . "\t" . '}' . "\n"
            . "\t" . '$form->getElement(\'' . strtolower($relation->getClass()) . 's\')->setValue($'
            . ZendR_Inflector::variable($relation->getAlias()) . ');' . "\n";
    }

    public function build()
    {
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($this->_schema->getClassNameController())
            ->setExtendedClass('Zend_Controller_Action');

        $class->setMethods(array(
            $this->getMethodIndex(),
            $this->getMethodListar(),
            $this->getMethodVer(),
            $this->getMethodAgregar(),
            $this->getMethodEditar(),
            $this->getMethodEliminar(),
            $this->getMethodRunForm(),
            $this->getMethodInitForm()
        ));
        
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);
        $file->setRequiredFiles(array(
            $this->_schema->getBaseRequire() . 'forms/' . $this->_schema->getClassModel() . 'Search.php',
            $this->_schema->getBaseRequire() . 'forms/' . $this->_schema->getClassModel() . '.php'
        ));

		if (!is_dir($this->_schema->getDirectoryPathController())) {
			mkdir($this->_schema->getDirectoryPathController());
		}

        if (!is_file($this->_schema->getFilePathController()) || $this->_schema->getForce() == 'force') {
            file_put_contents($this->_schema->getFilePathController(), $file->generate());
            echo "[OK] controlller has been created\n";
        } else {
            echo "[Warning] controlller has not been created, already exists\n";
        }
    }
}
