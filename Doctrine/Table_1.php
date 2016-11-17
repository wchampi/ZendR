<?php

class ZendR_Doctrine_Table extends Doctrine_Table
{
    const PROCESS_TITLE = 1;
    const PROCESS_FILTER = 2;

    /**
     * Nombre de la clase a ser usada en el mantenimiento a traves de Doctrine.
     */
    protected $_classReference = '';

    /**
     * deprecated
     */
    protected $_aliasClassReference = 'a';

    /**
     *
     */
    protected $_countAlias = 0;
    
    /**
     * Identifica las clases que se encuentren en las sentence parasada
     * como parametro spara asignarles un unico a lais a cada clase.
     *
     * @param array $class
     * @param array $structs
     * @param int $tipe El tipo de sentencia que se procesara
     *                  <br>Pueden ser:
     *                  <br>    Yk_Generic_Talbe::PROCESS_TITLE: para los titulos del mantenimiento
     *                  <br>    Yk_Generic_Talbe::PROCESS_FILTER: para filtros de listado
     */
    private function _identifyClass(array $class, array $sentences, $process = null)
    {
        if ($process == null) {
            $process = self::PROCESS_TITLE;
        }

        foreach ($sentences as $key => $filter) {
            $valueKey = '';
            if (!is_numeric($key)) {
                $valueKey = $key;
                if ($filter instanceOf Doctrine_Expression) {
                    $class[$this->_classReference]['columns'][$key] = $filter;
                    continue;
                }
            } else {
                if ($filter instanceOf Doctrine_Expression) {
                    // NO SE PROCESA SE AGREGA DIRECTAMENTE.
                    $expressions = explode(' ', $filter->getSql());
                    foreach ($expressions as $expression) {
                        if (strpos($expression, '.') !== false) {
                            array_push($sentences, $expression);
                        }
                    }
                }
            }
            $filter = trim($filter);
            $filterTemp = explode(' ', $filter);
            $filterTemp = explode('.', $filterTemp[0]);
            if (count($filterTemp) == 1) {
                if ($process == self::PROCESS_TITLE) {
                    if (!is_numeric($valueKey)) {
                        $class[$this->_classReference]['columns'][$valueKey] = $filter;
                    }
                }
            } else if (count($filterTemp) > 1) {
                $final = count($filterTemp) - 1;
                $campo = '';
                $childClass = '';
                for ($i = $final; $i >= 0; $i--) {
                    $finalClass = false;
                    $this->_countAlias++;
                    if ($i == $final) {
                        $campo = $filterTemp[$final];
                    } else {
                        if ($i == ($final - 1)) {
                            $finalClass = true;
                        }
                        if (!isset($class[$filterTemp[$i]])) {
                            $class[$filterTemp[$i]] = array(
                                'alias' => 't' . $this->_countAlias,
                                'columns' => array()
                            );
                            $parentClass = $filterTemp[$i];
                        }
                        if (!empty($childClass)) {
                            $class[$childClass]['parent'] = $filterTemp[$i];
                        }
                        if ($i == 0) {
                            $class[$filterTemp[$i]]['parent'] = $this->_classReference;
                        }
                        $childClass = $filterTemp[$i];
                    }
                    if ($finalClass) {
                        if (!isset($class[$filterTemp[$i]]['columns'][$key])) {
                            $class[$filterTemp[$i]]['columns'][$key] = $campo;
                        }
                    }
                }
            }
        }
        return $class;
    }

    /**
     * Remplaza la declaracion del las clases por su alias correspondiente en
     * la sentencia especidicada.
     *
     * @param string $sentence
     * @param array $class
     *
     * @return string
     */
    public function replaceClassForAlias($sentence, $class)
    {
        foreach ($class as $className => $classParams) {
            $sentence = str_replace($className . '.', $classParams['alias'] . '.', $sentence);
        }

        $sentenceArray = explode('.', $sentence);
        $sentence = $sentenceArray[count($sentenceArray) - 1];
        if (isset($sentenceArray[count($sentenceArray) - 2])) {
            $sentence = $sentenceArray[count($sentenceArray) - 2] . '.' . $sentence;
        }

        return $sentence;
    }
    
    /**
     * retorna una query con los filtros para listado de CRUD
     *
     * @param array $request
     * @param string $orderby
     * @return Doctrine_Query
     */
    public function getSqlFiltrar(array $request, $orderby = null, $titles = array())
    {
        $this->_classReference = $this->getComponentName();
        $class = array(
            $this->_classReference => array(
                'alias' => $this->_aliasClassReference
            )
        );

        if (is_array($orderby)) {
            $class = $this->_identifyClass($class, $orderby, self::PROCESS_TITLE);
        } else {
            $class = $this->_identifyClass($class, array($orderby), self::PROCESS_TITLE);
        }
        $class = $this->_identifyClass($class, $titles, self::PROCESS_TITLE);
        $class = $this->_identifyClass($class, array_keys($request), self::PROCESS_TITLE);
        
        $q = $this->createQuery('a');

        // ORDENANDO CLASES
        $classTemp = $class;
        $classOrder = array();
        do {
            foreach ($classTemp as $clasName => $classParams) {
                if ($clasName != $this->_classReference) {
                    if (isset($classOrder[ $class[$classParams['parent']]['alias'] ]) ) {
                        $classOrder[$classParams['alias']] = $clasName;
                        unset($classTemp[$clasName]);
                    } 
                } else {
                    $classOrder[$this->_aliasClassReference] = $this->_classReference;
                    unset($classTemp[$this->_classReference]);
                }
            }
        } while (count($classTemp) > 0);
        unset($classTemp);

        // AGREGANDO CLASES EN ORDEN PARA EVITAR ERROR
        foreach ($classOrder as $aliasClass => $classFrom) {
            if ($classFrom != $this->_classReference) {
                $classTemp = $class[$classFrom];
                $q->leftJoin($class[$classTemp['parent']]['alias'] . '.'
                    . $classFrom . ' ' . $classTemp['alias']);
            }
        }
        unset($classOrder);
        if (isset($request['filtro']) && isset($request['campo'])) {
        	if (!empty($request['filtro']) && !empty($request['campo'])) {
        		$q->addWhere("LOWER(" . $this->replaceClassForAlias($request['campo'], $class) . ") LIKE ?", '%'
        			. ZendR_String::parseString($request['filtro'])->toLower()->__toString() . '%');
        	} elseif($request['filtro'] == '0') {
        		$q->addWhere($request['campo'] . " = '0' OR " . $request['campo'] . " IS NULL");
        	}
        }

        // PENDIENTE MEMORIA DE ORDEN ANTERIOR EN SESSION
        if (!is_array($orderby)) {
            $columnOrderBy = array($orderby);
        }
        
        foreach ($columnOrderBy as $order) {
            $tmpOrder = '';
            $pref = ' asc';
            if (substr($order, -4) == ' asc') {
                $tmpOrder = substr($order, 0, -4);
            } else {
                $pref = ' desc';
                $tmpOrder = substr($order, 0, -5);
            }
            if (isset($structsOrder[$tmpOrder])) {
                $indexOrder = Yk_String::parseString($structsOrder[$tmpOrder])->toLower()->__toString();
                if (isset($structs[$indexOrder])) {
                    $tmpOrder = $structs[$indexOrder];
                } else {
                    $tmpOrder = $structsOrder[$tmpOrder];
                }
            }
            $q->orderBy($this->replaceClassForAlias($tmpOrder, $class) . $pref);
        }

        return $q;
    }

    /**
     * Obtiene registros de la bd en forma de Doctrine_Collection ò Doctrine_Query
     *
     * @param string|array $where
     * @param string|array $orderby
     * @param integer $limit
     * @param boolean $collection
     * @return Doctrine_Collection|Doctrine_Query
     */
    public function obtenerPor($where,  $orderby = null, $limit = null, $collection = true)
    {
        $q = $this->createQuery('a');

        if (is_array($where)) {
            foreach ($where as $cond => $values) {
                if (is_numeric($cond)) {
                    $q->addWhere($values);
                } else {
                    $q->addWhere($cond, $values);
                }
            }
        } elseif (is_string ($where)) {
            $q->addWhere($where);
        }

        if (is_array($orderby)) {
            foreach ($orderby as $order) {
                $q->orderBy($order);
            }
        } elseif(is_string($orderby)) {
            $q->orderBy($orderby);
        }

        if ((int)$limit > 0) {
            $q->limit((int)$limit);
        }
        return $collection ? $q->execute() : $q;
    }

    /**
     * Obtiene registros de la bd en forma de Doctrine_Collection ò Doctrine_Query
     *
     * @param string|array $where
     * @param string|array $orderby
     * @return Doctrine_Record
     */
    public function obtenerOnePor($where = null,  $orderby = null)
    {
        $q = $this->createQuery('a');

        if (is_array($where)) {
            foreach ($where as $cond => $values) {
                if (is_numeric($cond)) {
                    $q->addWhere($values);
                } else {
                    $q->addWhere($cond, $values);
                }
            }
        } else {
            $q->addWhere($where);
        }

        if (is_array($orderby)) {
            foreach ($orderby as $order) {
                $q->orderBy($order);
            }
        } elseif(is_string($orderby)) {
            $q->orderBy($orderby);
        }

        return $q->fetchOne();
    }

    public function eliminarPor($where = null)
    {
        $q = $this->createQuery('a')->delete();

        if (is_array($where)) {
            foreach ($where as $cond => $values) {
                if (is_numeric($cond)) {
                    $q->addWhere($values);
                } else {
                    $q->addWhere($cond, $values);
                }
            }
        } else {
            $q->addWhere($where);
        }

        return $q->execute();
    }

    /**
     * Return un array asociativo donde los indices del array son los valores
     * de la columna $index y los valores son los de la columna $label.
     *
     * @param string $index OPCIONAL Nombre de la columna que sera el indice,
     *                      por defecto es 'id'
     * @param string $label OPCIONAL Nombre de la columna que sera mostrado en
     *                      el combo, por defecto es 'nombre'.
     * @param array $filters OPCIONAL filtros a aplicar a los resultados mostrados.
     *
     * @return array
     */
    public function forCombo2($index = 'id', $label = 'nombre', $filters = null)
    {
        $query = $this->createQuery()->select($index . ', ' . $label);
        if (is_array($filters)) {
            foreach ($filters as $filter => $value) {
                if (!is_numeric($filter)) {
                    $query->addWhere($filter, $value);
                } else {
                    $query->addWhere($value);
                }
            }
        }
		$query->addOrderBy($label);
        return $query->execute()->toKeyValueArray($index, $label);
    }
    
    public function forCombo($index = 'id', $label = 'nombre', $filters = null)
    {
        $q = $this->createQuery('a');
        if (is_array($filters)) {
            foreach ($filters as $filter => $value) {
                if (!is_numeric($filter)) {
                    $q->addWhere($filter, $value);
                } else {
                    $q->addWhere($value);
                }
            }
        } elseif(is_string($filters)) {
            $q->addWhere($filters);
        }

        $where = $q->getDqlPart('where');
        if (count($where) > 0) {
            $where = " WHERE " . implode(' ', $where);
        } else {
            $where = '';
        }
        
        $params = $q->getParams();
        $query = "SELECT $index, $label FROM " . $this->getTableName() . " " . $where . " ORDER BY $label ASC";
        return $this->getConnection()->execute($query, $params['where'])->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE);
    }

    public function queryKeyGroup($query)
    {
        return $this->getConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
    }

    public function queryKeyArray($query)
    {
        return $this->getConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
    }
    
    public function queryKeyValue($query, $params = array())
    {
        return $this->getConnection()->execute($query, $params)->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE);
    }

    public function fetchAll($query)
    {
        return $this->getConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne($query)
    {
        return $this->getConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
    }

    private function _prepareName($val)
    {
        return "`" . str_replace('`', '', $val) . "`";
    }
    
    public function insertMultiple($columns, $rows)
    {
        $tableName = self::_prepareName($this->getTableName());

        $columnsName = array();
        foreach ($columns as $column) {
            $columnsName[] = self::_prepareName($column);
        }

        $paramsValues = array();
        $rowsValues = array();
        foreach ($rows as $row) {
            $values = array();
            foreach ($row as $val) {
                $paramsValues[] = $val;
                $values[] = '?';
            }
            $rowsValues[] = "(" . implode(',', $values) . ")";
        }

        $numberItemsInsert = 2000;
        $position = 0;
        while ($position <= (count($rowsValues) - 1)) {
            $values = array_slice($rowsValues, $position, $numberItemsInsert);

            $query = "INSERT INTO $tableName (" . implode(',', $columnsName) . ")"
                . " VALUES " . implode(',', $values);
            $this->getConnection()->exec($query, $paramsValues);

            $position += $numberItemsInsert;
        }
    }

    public function fetchOneArrayBy($where, $orderby = null)
    {
        return $this->getConnection()->execute($this->_prepareSelectSimple($where, $this->getTableName(), $orderby, 1))->fetch();
    }

    private function _prepareSelectSimple($where, $table, $orderby = null, $limit = null)
    {
        $query = "SELECT * FROM $table"
            . self::_prepareWhere($where)
            . self::_prepareOrderBy($orderby)
            . self::_prepareLimit($limit);
        //echo $query;
        return $query;
    }

    public function _prepareWhere($where)
    {
        if (is_string ($where)) {
            $where = array($where);
        }

        if (is_array($where)) {
            return ' WHERE ' . implode(' AND ', $where);
        }
        return '';
    }

    public function _prepareOrderBy($orderby)
    {
        if (is_string ($orderby)) {
            $orderby = array($orderby);
        }

        if (is_array($orderby)) {
            return ' ORDER BY ' . implode(' , ', $orderby);
        }
        return '';
    }

    public function _prepareLimit($limit)
    {
        if ((int)$limit > 0) {
            return ' LIMIT ' . $limit;
        }

        return '';
    }

    public function update($fields, $identifier)
    {
        $this->getConnection()->update($this, $fields, $identifier);
    }

    public function insert($fields)
    {
        $this->getConnection()->insert($this, $fields);
    }
    
    public function removeAcentos($string)
    {
        $string = ZendR_String::parseString(strip_tags(trim($string)))->toLower()->toUTF8()->__toString();

        $string = preg_replace('[aáàãâä]','a',$string);
        $string = preg_replace('[eéèêë]','e',$string);
        $string = preg_replace('[iíìîï]','i',$string);
        $string = preg_replace('[oóòõôö]','o',$string);
        $string = preg_replace('[uúùûü]','u',$string);
        $string = preg_replace('[ç]','c',$string);
        $string = preg_replace('[ñ]','n',$string);

       return $this->getConnection()->quote($string);
    }


    public function removeAcentosSQL($string){
        
        $contador = 0;

        $acentos = explode(",","á,à,ã,â,ä,é,è,ê,ë,í,ì,î,ï,ó,ò,õ,ô,ö,ú,ù,û,ü,ç,ñ");
        $sinAcentos = explode(",","a,a,a,a,a,e,e,e,e,i,i,i,i,o,o,o,o,o,u,u,u,u,c,n");

        $query[$contador] = "REPLACE($string,'a','a')";
        $contador++;

        $x = 0;
        foreach($acentos as $val){
            $query[$contador] = "REPLACE({$query[$contador-1]},'$val','{$sinAcentos[$x]}')";
            $contador++;
            $query[$contador] = "REPLACE({$query[$contador-1]},'".strtoupper($val)."','{$sinAcentos[$x]}')";
            $contador++;
            $x++;
        }

        $final = $query[$contador-1];
        $final = ZendR_String::parseString($final)->toUTF8()->__toString();

        return $final;
    }

}
