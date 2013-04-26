<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Doctrine_Import_Builder extends Doctrine_Import_Builder
{
    public function buildTableClassDefinition($className, $options = array())
    {
        $extends = 'ZendR_Doctrine_Table';

		$content = '<?php' . PHP_EOL . PHP_EOL
			. 'class ' . $className . ' extends ' . $extends . PHP_EOL
			. '{'
			. PHP_EOL
			. PHP_EOL
			. '}';

        return $content;
    }
	
    /**
     * writeTableClassDefinition
     *
     * @return void
     */
    public function writeTableClassDefinition(array $definition, $path, $options = array())
    {
        $className = $definition['tableClassName'];
        $fileName = $className . $this->_suffix;
        $writePath = $path . DIRECTORY_SEPARATOR . 'table' . DIRECTORY_SEPARATOR . $fileName;

        $content = $this->buildTableClassDefinition($className, $options);

        Doctrine_Lib::makeDirectories(dirname($writePath));

        Doctrine_Core::loadModel($className, $writePath);

        if ( ! file_exists($writePath)) {
            file_put_contents($writePath, $content);
        }
    }

    private function _getFunctionTable($definition)
    {
        return "\t" . '/**' . PHP_EOL
            . "\t" . '  *' . PHP_EOL
            . "\t" . '  * @return ' . $definition['className'] . 'Table' . PHP_EOL
            . "\t" . '  */' . PHP_EOL
            . "\t" . 'public static function table()' . PHP_EOL
            . "\t" . '{' . PHP_EOL
            . "\t\t" . 'return Doctrine::getTable(\'' . $definition['className'] . '\');' . PHP_EOL
            . "\t" . '}' . PHP_EOL;
    }
    
    public function writeDefinition(array $definition)
    {
        $this->_baseClassName = 'ZendR_Sf_Record';

        if ($definition['is_base_class'] != '1') {
            self::$_tpl = '/**'
                . '%s' . PHP_EOL
                . ' */' . PHP_EOL
                . '%sclass %s extends %s' . PHP_EOL
                . '{'
                . '%s' . PHP_EOL
                . '%s' . PHP_EOL
                . $this->_getFunctionTable($definition)
                . '}';
        } else {
            self::$_tpl = '/**'
                . '%s' . PHP_EOL
                . ' */' . PHP_EOL
                . '%sclass %s extends %s' . PHP_EOL
                . '{'
                . '%s' . PHP_EOL
                . '%s' . PHP_EOL
                . '}';
        }

        return parent::writeDefinition($definition);
    }
}