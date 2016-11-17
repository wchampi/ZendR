<?php
/**
 * Modificado por Wilson Ramiro Champi Tacuri
 *
 * @author Symfony
 */

abstract class ZendR_Sf_Record extends Doctrine_Record
{

    static protected
    $_defaultCulture = 'en';

    /**
     * Initializes internationalization.
     *
     * @see Doctrine_Record
     */
    public function construct()
    {

    }

    /**
     * Returns the current record's primary key.
     *
     * This a proxy method to {@link Doctrine_Record::identifier()} for
     * compatibility with a Propel-style API.
     *
     * @return mixed The value of the current model's last identifier column
     */
    public function getPrimaryKey()
    {
        $identifier = (array) $this->identifier();
        return end($identifier);
    }

    /**
     * Function require by symfony >= 1.2 admin generators.
     *
     * @return boolean
     */
    public function isNew()
    {
        return!$this->exists();
    }

    /**
     * Returns a string representation of the record.
     *
     * @return string A string representation of the record
     */
    public function __toString()
    {
        $guesses = array('name',
            'title',
            'description',
            'subject',
            'keywords',
            'id');

        // we try to guess a column which would give a good description of the object
        foreach ($guesses as $descriptionColumn) {
            try {
                return (string) $this->get($descriptionColumn);
            } catch (Exception $e) {

            }
        }

        return sprintf('No description for object of class "%s"', $this->getTable()->getComponentName());
    }

    /**
     * Provides getter and setter methods.
     *
     * @param  string $method    The method name
     * @param  array  $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     */
    public function __call($method, $arguments)
    {
        $string = false;
        $failed = false;
        try {
            if (in_array($verb = substr($method, 0, 3), array('set', 'get'))) {
                $name = substr($method, 3);

                $table = $this->getTable();
                if ($table->hasRelation($name)) {
                    $entityName = $name;
                } else if ($table->hasField($fieldName = $table->getFieldName($name))) {
                    $entityNameLower = strtolower($fieldName);
                    if ($table->hasField($entityNameLower)) {
                        $entityName = $entityNameLower;
                    } else {
                        $entityName = $fieldName;
                    }
                } else {
                    $underScored = $table->getFieldName(ZendR_Sf_Inflector::underscore($name));
                    if ($table->hasField($underScored) || $table->hasRelation($underScored)) {
                        $string = true;
                        $entityName = $underScored;
                    } else if ($table->hasField(strtolower($name)) || $table->hasRelation(strtolower($name))) {
                        $entityName = strtolower($name);
                    } else {
                        $camelCase = $table->getFieldName(ZendR_Sf_Inflector::camelize($name));
                        $camelCase = strtolower($camelCase[0]) . substr($camelCase, 1, strlen($camelCase));
                        if ($table->hasField($camelCase) || $table->hasRelation($camelCase)) {
                            $entityName = $camelCase;
                        } else {
                            $entityName = $underScored;
                        }
                    }
                }

                return call_user_func_array(
                    array($this, $verb),
                    array_merge(array($entityName), $arguments)
                );
            } else {
                $failed = true;
            }
        } catch (Exception $e) {
            $failed = true;
        }
        if ($failed) {
            try {
                return parent::__call($method, $arguments);
            } catch (Doctrine_Record_UnknownPropertyException $e2) {

            }

            if (isset($e) && $e) {
                throw $e;
            } else if (isset($e2) && $e2) {
                throw $e2;
            }
        }
    }

    protected function  _get($fieldName, $load = true)
    {
        $value = parent::_get($fieldName, $load);
        if (is_string($value)) {
            $value = trim($value);
            if (empty ($value)) {
                return $value;
            }

            if (Zend_Registry::isRegistered('date_format')) {
                $dateFormat = Zend_Registry::get('date_format');

                $definition = $this->getTable()->getColumnDefinition($fieldName);
                if ($definition['type'] == 'date') {
                    if (trim($value) != '' && strpos($value, '0000-00-00') === false) {
                        $date = new Zend_Date($value, 'Y-m-d');
                        return $date->toString($dateFormat);
                    } else {
                        return '';
                    }
                }

                if ($definition['type'] == 'timestamp') {
                    if (trim($value) != '' && strpos($value, '0000-00-00') === false) {
                        $date = new Zend_Date($value, 'Y-m-d H:i:s');
                        return $date->toString($dateFormat . " H:i:s");
                    } else {
                        return '';
                    }
                }
            }

            return ZendR_String::parseString($value)->toUTF8()->__toString();
        }
        return $value;
    }

    public function  _set($fieldName, $value, $load = true)
    {
        if (is_string($value)) {
            $value = trim($value);
            if (empty ($value)) {
                return parent::_set($fieldName, null, $load);
            }

            if (Zend_Registry::isRegistered('date_format')) {
                $dateFormat = Zend_Registry::get('date_format');
                
                $definition = $this->getTable()->getColumnDefinition($fieldName);
                if ($definition['type'] == 'date' && trim($value) != '') {
                    
                    $date = new Zend_Date($value, $dateFormat);
                    return parent::_set($fieldName, $date->toString("Y-m-d"), $load);
                }

                if ($definition['type'] == 'timestamp' && trim($value) != '') {
                    $date = new Zend_Date($value, $dateFormat . ' H:i:s');
                    return parent::_set($fieldName, $date->toString('Y-m-d H:i:s'), $load);
                }
            }

            $value = ZendR_String::parseString($value)->toUTF8()->__toString();

            return parent::_set($fieldName, $value, $load);
        }
        return parent::_set($fieldName, $value, $load);
    }
}