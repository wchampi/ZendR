<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Crud_BuilderTable
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

	public function getMethodFiltrar()
    {
		$parRequest = new Zend_CodeGenerator_Php_Parameter();
        $parRequest->setName('request');
        $parRequest->setType('array');

		$parOrderBy = new Zend_CodeGenerator_Php_Parameter();
        $parOrderBy->setName('orderBy');
        $parOrderBy->setDefaultValue('null');

        $method = new Zend_CodeGenerator_Php_Method();
        $method->setName('filtrar');
		$method->setParameters(array($parRequest, $parOrderBy));

        $bodyColumns = 'if (isset($request[\'filtro\'])) {' . "\n"
				. "\t" . 'if (!empty($request[\'filtro\'])) {' . "\n"
				. "\t\t" . '$q->addWhere("LOWER(" . $request[\'campo\'] . ") LIKE ?", \'%\'' . "\n"
				. "\t\t\t" . '. ZendR_String::parseString($request[\'filtro\'])->toLower() . \'%\');' . "\n"
				. "\t" . '} else {' . "\n"
                . "\t\t" . '$q->addWhere($request[\'campo\'] . " = \'0\' OR " . $request[\'campo\'] . " IS NULL");' . "\n"
                . "\t" . '}' . "\n"
				. '}' . "\n\n"
			;

		$body = '$q = $this->getSqlFiltrar($request, $orderBy);' . "\n"
			. 'return $q;'
		;

        $method->setBody($body);

        return $method;
    }

	public function build()
	{
		if (is_file($this->_schema->getFilePathTable())) {
			$file = Zend_CodeGenerator_Php_File::fromReflectedFileName($this->_schema->getFilePathTable());
			if ($file->getClass()->hasMethod('filtrar')) {
				echo "[Warning] A method by name filtrar already exists in class "
					. $this->_schema->getClassModel() . "Table\n";
			} else {
				$file->getClass()->setMethod($this->getMethodFiltrar());
                echo "[OK] method filtrar has been created in class"
                    . $this->_schema->getClassModel() . "Table\n";
			}
		} else {
			$class = new Zend_CodeGenerator_Php_Class();
			$class->setName($this->_schema->getClassModel() . 'Table');
			$class->setExtendedClass('Doctrine_Table');
			$class->setMethod($this->getMethodFiltrar());
			
			$file = new Zend_CodeGenerator_Php_File();
			$file->setClass($class);

			if (!is_dir($this->_schema->getDirectoryPathTable())) {
				mkdir($this->_schema->getDirectoryPathTable());
			}
		}

        file_put_contents($this->_schema->getFilePathTable(), $file->generate());
	}
}