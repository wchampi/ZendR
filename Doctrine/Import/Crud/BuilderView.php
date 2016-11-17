<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Crud_BuilderView
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

	public function buildListar()
	{
        $primaryColumn = $this->_schema->getTable()->getIdentifier();
        $htmlColumns = '';
        $jsBooleanColumns = '';
        $existeImagen = false;
        $jsImage = '';
        $module = $this->_schema->getModuleName() == 'default' ? 'default/' : '';
        foreach ($this->_schema->getColumnsList() as $column => $property) {
            $notNull = false;
            if (isset($property['notnull'])) {
                if (trim($property['notnull']) == '1') {
                    $notNull = true;
                }
            }
            $htmlNotNull = '';
            if (!$notNull) {
                $htmlNotNull = '&nbsp;';
            }
            if ($property['type'] == 'boolean') {
                $htmlColumn = "\t\t\t\t\t\t" . '<input type="checkbox" name="' . $column . 's[]" onclick="cambiar'
                    . $this->_schema->getNameFunction($column) . '(this)" value="<?php echo $' . $this->_schema->getControllerName() 
                    . '->' . $this->_schema->getNameFunctionGet($primaryColumn) . '() ?>" <?php if ($' . $this->_schema->getControllerName()  
                    . '->' . $this->_schema->getNameFunctionGet($column) . '()): ?>checked="checked"<?php endif ?> />' . "\n";

                $jsBooleanColumns .= "\t" .  'var cambiar' . $this->_schema->getNameFunction($column) . ' = function (checkbox) {' . "\n"
                    . "\t\t" . '$(\'#pMensaje\').html(\'\');' . "\n"
                    . "\t\t" . 'var dateActual = new Date();' . "\n"
                    . "\t\t" . '$.ajax({' . "\n"
                    . "\t\t\t" . 'url: \'<?php echo $this->baseUrl(\'' . $module . 'util/cambiar-estado/modelo/' 
                    . $this->_schema->getClassModel() . '/id/\') ?>\' + checkbox.value + \'/atributo/' . $column 
                    . '/val/\' + checkbox.checked + \'/r/\' + dateActual.getTime(),' . "\n"
                    . "\t\t\t" . 'success: function (html) {' . "\n"
                    . "\t\t\t\t" . 'if (html == \'ok\') {' . "\n"
                    . "\t\t\t\t\t" . '$(\'#pMensaje\').html(\'<div class="msgConfirm">' . Zend_Registry::get('Zend_Translate')->_('Record updated successfully') . '<\/div>\');' . "\n"
                    . "\t\t\t\t" . '}' . "\n"
                    . "\t\t\t" . '}' . "\n"
                    . "\t\t" . '});' . "\n"
                    . "\t" . '}' . "\n";
            } else {
                if (strpos($column, 'image') !== false || strpos($column, 'foto') !== false
                    || strpos($column, 'icono') !== false || strpos($column, 'logo') !== false) {
                    $htmlColumn = "\t\t\t\t\t\t" . '<?php if (trim($' . $this->_schema->getControllerName() 
                        . '->' . $this->_schema->getNameFunctionGet($column)  . '()) != \'\'): ?>' . "\n"
                        . "\t\t\t\t\t\t\t" . '<a href="javascript:void(0)" onclick="ampliarImagen(\'<?php echo $this->baseUrl(\'uploads/'
                        . $this->_schema->getControllerNameIze() . '/\' . $' . $this->_schema->getControllerName() 
                        . '->' . $this->_schema->getNameFunctionGet($column)  . '()) ?>\')" >' . "\n"
                        . "\t\t\t\t\t\t\t\t" . '<img src="<?php echo $this->thumb(\'uploads/' . $this->_schema->getControllerNameIze()
                        . '/\' . $' . $this->_schema->getControllerName() . '->' . $this->_schema->getNameFunctionGet($column)  
                        . '(), 0, 20, true) ?>" alt="" style="border: 0" />' . "\n"
                        . "\t\t\t\t\t\t\t" . '</a>' . "\n"
                        . "\t\t\t\t\t\t" . '<?php endif ?>' . "\n"
                        . "\t\t\t\t\t\t" . '&nbsp;' . "\n";
                    $existeImagen = true;
                } else {
                    $htmlColumn = "\t\t\t\t\t\t" . '<?php echo $' . $this->_schema->getControllerName()
                        . '->' . $this->_schema->getNameFunctionGet($column)  . '() ?>' . $htmlNotNull . "\n";
                }
            }
            $htmlColumns .= "\t\t\t\t\t" . '<td>' . "\n"
                . $htmlColumn
                . "\t\t\t\t\t" . '</td>' . "\n";
        }

        $htmlFoot = '';
        if ($jsBooleanColumns != '') {
            $htmlFoot = '<div id="pMensaje"></div>';
        }

        if ($existeImagen) {
            $jsImage = "\t" . '$(function() {' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').dialog({' . "\n"
                . "\t\t\t" . 'bgiframe: true,' . "\n"
                . "\t\t\t" . 'autoOpen: false,' . "\n"
                . "\t\t\t" . 'width: \'auto\'' . "\n"
                . "\t\t" . '});' . "\n"
                . "\t" . '});' . "\n"
                . "\t" . 'var ampliarImagen = function (urlImagen) {' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').dialog(\'close\');' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').html(\'<img src="\' + urlImagen + \'" alt="Imagen Amplia"  \/>\');' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').dialog(\'open\');' . "\n"
                . "\t" . '}' . "\n";
            $htmlImage = '<div id="imagenAmplia" ></div>';
        }

        if ($primaryColumn != '') {
            $columnDescription = $this->_schema->obtenerColumnDescription() != '' ?
                $this->_schema->obtenerColumnDescription() : $primaryColumn;
            
            $htmlColumns .= "\t\t\t\t\t" . '<td>' . "\n"
                . "\t\t\t\t\t\t" . '<a href="<?php echo $this->baseUrl(\'' . $this->_schema->getUrlVer() . ') ?>" title="' . Zend_Registry::get('Zend_Translate')->_('View') . '" >' . "\n"
                . "\t\t\t\t\t\t\t" . '<img src="<?php echo $this->baseUrl(\'zendr/css/graphics/ico_ver.gif\') ?>"'
                . ' alt="' . Zend_Registry::get('Zend_Translate')->_('View') . '" style="border: 0px" />' . "\n"
                . "\t\t\t\t\t\t" . '</a>&nbsp;&nbsp;' . "\n"
                . "\t\t\t\t\t\t" . '<a href="<?php echo $this->baseUrl(\'' . $this->_schema->getUrlEditar() . ') ?>" title="' . Zend_Registry::get('Zend_Translate')->_('Edit') . '" >' . "\n"
                . "\t\t\t\t\t\t\t" . '<img src="<?php echo $this->baseUrl(\'zendr/css/graphics/ico_edit.png\') ?>"' 
                . ' alt="' . Zend_Registry::get('Zend_Translate')->_('Edit') . '" style="border: 0px" />' . "\n"
                . "\t\t\t\t\t\t" . '</a>&nbsp;&nbsp;' . "\n"
                . "\t\t\t\t\t\t" . '<a href="#" onclick="eliminar' . $this->_schema->getClassModel() . '(\'<?php echo $'
                . $this->_schema->getControllerName() . '->' . $this->_schema->getNameFunctionGet($primaryColumn) . '() ?>\', \'<?php echo htmlspecialchars($'
                . $this->_schema->getControllerName() . '->' . $this->_schema->getNameFunctionGet($columnDescription)
                . '()) ?>\')" title="' . Zend_Registry::get('Zend_Translate')->_('Delete') . '" >' . "\n"
                . "\t\t\t\t\t\t\t" . '<img src="<?php echo $this->baseUrl(\'zendr/css/graphics/ico_trash.gif\') ?>"'
                . ' alt="' . Zend_Registry::get('Zend_Translate')->_('Delete') . '" style="border: 0px" />' . "\n"
                . "\t\t\t\t\t\t" . '</a>' . "\n"
                . "\t\t\t\t\t" . '</td>' . "\n";
        }

		$html = '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n"
            . "\t" . 'var eliminar' . $this->_schema->getClassModel() . ' = function (id, nombre) {' . "\n"
            . "\t\t" . 'if (confirm(\'' . Zend_Registry::get('Zend_Translate')->_('Are you sure to delete') . ' "\' + nombre + \'"?\')) {' . "\n"
            . "\t\t\t" . 'var dateActual = new Date();' . "\n"
            . "\t\t\t" . 'self.location.href = \'<?php echo $this->baseUrl(\'' . $this->_schema->getUrlController() . Zend_Registry::get('Zend_Translate')->_('delete') . '/id/\') ?>\' + id  + \'/r/\' + dateActual.getTime();' . "\n"
            . "\t\t" . '}' . "\n"
            . "\t" . '}' . "\n"
            . $jsBooleanColumns
            . $jsImage
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>' . "\n"
            . '<div id="Search">' . "\n"
			. "\t" . '<div class="searchLeft">' . "\n"
			. "\t\t" . '<input type="button" name="' . Zend_Registry::get('Zend_Translate')->_('Add') . '" value="' . Zend_Registry::get('Zend_Translate')->_('Add') . '"'
			. ' onclick="self.location.href=\''
			. '<?php echo $this->baseUrl(\'' . $this->_schema->getUrlAgregar() . '\') ?>\'" class="btnAction"  />' . "\n"
			. "\t" . '</div>' . "\n"
			. "\t" . '<div class="searchRight">' . "\n"
			. "\t\t" . '<?php echo $this->form->setAction($this->url()) ?>' . "\n"
			. "\t" . '</div>' . "\n"
			. '</div>' . "\n"
            . '<?php if ($this->paginator->count() > 0): ?>' . "\n"
			. '<div id="Listado">' . "\n"
			. "\t" . '<table width="90%" class="tableGral" cellpadding="0" cellspacing="0">' . "\n"
			. "\t\t" . '<?php echo $this->head ?>' . "\n"
			. "\t\t" . '<tbody>' . "\n"
			. "\t\t\t" . '<?php foreach ($this->paginator as $' . $this->_schema->getControllerName() . ') : ?>' . "\n"
			. "\t\t\t\t" . '<tr>' . "\n"
			. $htmlColumns
			. "\t\t\t\t" . '</tr>' . "\n"
			. "\t\t\t" . '<?php endforeach ?>' . "\n"
			. "\t\t" . '</tbody>' . "\n"
			. "\t" . '</table>' . "\n"
			. '</div>' . "\n"
            . '<?php endif ?>' . "\n"
			. '<?php echo $this->paginator ?>' . "\n"
            . $htmlFoot . "\n"
            . $htmlImage;
        if (!is_file($this->_schema->getFilePathViewListar()) || $this->_schema->getForce() == 'force') {
            file_put_contents($this->_schema->getFilePathViewListar(), $html);
            echo "[OK] script list.phtml has been created\n";
        } else {
            echo "[Warning] script list.phtml has not been created, already exists\n";
        }
	}

	public function buildForm()
	{
		$html = '<?php if ($this->' . $this->_schema->getControllerName() . '): ?>' . "\n"
            . '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n"
            . "\t" . 'var eliminar' . $this->_schema->getClassModel() . ' = function () {'  . "\n"
            . "\t\t" . 'if (confirm(\'' . Zend_Registry::get('Zend_Translate')->_('Are you sure "Delete"?') . '\')) {'  . "\n"
            . "\t\t\t" . 'self.location.href = \'<?php echo $this->baseUrl(\''
            . $this->_schema->getUrlEliminar('this->') . ') ?>\';'  . "\n"
            . "\t\t" . '}' . "\n"
            . "\t" . '}' . "\n"
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>' . "\n"
            . '<?php endif ?>' . "\n"
            . '<?php echo $this->form->setAction($this->url()) ?>' . "\n"
            . '<?php if ($this->responseError): ?>' . "\n"
            . "\t" . '<div class="msgError"><?php echo $this->responseError ?></div>' . "\n"
            . '<?php endif ?>';
        if (!is_file($this->_schema->getFilePathViewForm()) || $this->_schema->getForce() == 'force') {
            file_put_contents($this->_schema->getFilePathViewForm(), $html);
            echo "[OK] script form.phtml has been created\n";
        } else {
            echo "[Warning] script form.phtml has not been created, already exists\n";
        }
	}

    public function buildVer()
	{
        $existeImagen = false;
        $primaryColumn = $this->_schema->getTable()->getIdentifier();
        $htmlColumns = '';
        foreach ($this->_schema->getColumnsForm() as $column => $property) {
            if (strpos($column, 'image') !== false || strpos($column, 'foto') !== false
                || strpos($column, 'icono') !== false || strpos($column, 'logo') !== false) {
                $existeImagen = true;
            }
            $htmlColumns .= "\t" .  '<div id="' . $this->_schema->getControllerName() . '_'
                . $column . '-element" class="fields">' . "\n"
                . $this->renderElementVer($column, $property)
                . "\t" .  '</div>' . "\n";
        }
        
        if ($existeImagen) {
            $jsImage = "\t" . '$(function() {' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').dialog({' . "\n"
                . "\t\t\t" . 'bgiframe: true,' . "\n"
                . "\t\t\t" . 'autoOpen: false,' . "\n"
                . "\t\t\t" . 'width: \'auto\'' . "\n"
                . "\t\t" . '});' . "\n"
                . "\t" . '});' . "\n"
                . "\t" . 'var ampliarImagen = function (urlImagen) {' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').dialog(\'close\');' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').html(\'<img src="\' + urlImagen + \'" alt="Imagen Amplia"  \/>\');' . "\n"
                . "\t\t" . '$(\'#imagenAmplia\').dialog(\'open\');' . "\n"
                . "\t" . '}' . "\n";
            $htmlImage = '<div id="imagenAmplia" ></div>';
        }
        $js = '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n"
            . "\t" . 'var eliminar' . $this->_schema->getClassModel() . ' = function () {'  . "\n"
            . "\t\t" . 'if (confirm(\'' . Zend_Registry::get('Zend_Translate')->_('Are you sure "Delete"?') . '\')) {'  . "\n"
            . "\t\t\t" . 'self.location.href = \'<?php echo $this->baseUrl(\''
            . $this->_schema->getUrlEliminar('this->') . ') ?>\';'  . "\n"
            . "\t\t" . '}' . "\n"
            . "\t" . '}' . "\n"
            . $jsImage
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>' . "\n";

		$html = $js
            . '<fieldset class="fieldset" id="fieldset-gDatos">' . "\n"
            . $htmlColumns
            . '</fieldset>' . "\n"
            . '<div class="buttonContent">' . "\n"
			. "\t" . '<?php echo $this->form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('edit') . '\') ?>' . "\n"
            . "\t" . '<?php echo $this->form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('list') . '\') ?>' . "\n"
            . "\t" . '<?php echo $this->form->getElement(\'' . Zend_Registry::get('Zend_Translate')->_('delete') . '\') ?>' . "\n"
            . '</div>' . "\n"
            . $htmlImage;
        
        if (!is_file($this->_schema->getFilePathViewVer()) || $this->_schema->getForce() == 'force') {
            file_put_contents($this->_schema->getFilePathViewVer(), $html);
            echo "[OK] script ver.phtml view has been created\n";
        } else {
            echo "[Warning] script ver.phtml has not been created, already exists\n";
        }
	}

    public function renderElementVer($column, $property)
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
			$foreignColDes = $this->_schema->obtenerColumnDescription($relation->getClass());

            $element = "\t\t" . '<label class="label optional">' . $this->_schema->getModelNameIze($relation->getAlias()) . ' :</label>' . "\n"
                . "\t\t" . '<strong class="label optional"><?php echo $this->' . $this->_schema->getControllerName()
                . '->get' . $relation->getAlias() . '()->' . $this->_schema->getNameFunctionGet($foreignColDes)  . '() ?></strong>' . "\n";
		} else {
            if (strpos($column, 'image') !== false || strpos($column, 'foto') !== false
                || strpos($column, 'icono') !== false || strpos($column, 'logo') !== false) {
                $element = "\t\t" . '<label class="label optional">' . ZendR_Inflector::label($column) . ' :</label>' . "\n"
                    . "\t\t" . '<strong class="label optional">' . "\n"
                    . "\t\t\t" . '<?php if (trim($this->' . $this->_schema->getControllerName()
                    . '->' . $this->_schema->getNameFunctionGet($column)  . '()) != \'\'): ?>' . "\n"
                    . "\t\t\t\t" . '<a href="javascript:void(0)" onclick="ampliarImagen(\'<?php echo $this->baseUrl(\'uploads/'
                    . $this->_schema->getControllerNameIze() . '/\' . $this->' . $this->_schema->getControllerName()
                    . '->' . $this->_schema->getNameFunctionGet($column)  . '()) ?>\')" >' . "\n"
                    . "\t\t\t\t\t" . '<img src="<?php echo $this->baseUrl(\'uploads/' . $this->_schema->getControllerNameIze()
                    . '/\' . $this->' . $this->_schema->getControllerName() . '->' . $this->_schema->getNameFunctionGet($column)
                    . '()) ?>" alt="" height="20" style="border: 0px" />' . "\n"
                    . "\t\t\t\t" . '</a>' . "\n"
                    . "\t\t\t" . '<?php endif ?>' . "\n"
                    . "\t\t\t" . '&nbsp;' . "\n"
                    . "\t\t" . '</strong>' . "\n";
            } else {
                $element = "\t\t" . '<label class="label optional">' . ZendR_Inflector::label($column) . ' :</label>' . "\n"
                    . "\t\t" . '<strong class="label optional"><?php echo $this->' . $this->_schema->getControllerName()
                    . '->' . $this->_schema->getNameFunctionGet($column)  . '() ?>&nbsp;</strong>' . "\n";
            }
        }

        return $element;
    }

	public function build()
    {
		if (!is_dir($this->_schema->getDirectoryPathView())) {
			mkdir($this->_schema->getDirectoryPathView());
		}
		
        $this->buildForm();
		$this->buildListar();
        $this->buildVer();
    }
}