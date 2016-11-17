<?php

/**
 * Modificado por Wilson Ramiro Champi Tacuri
 *
 * @author Symfony
 */

require_once(dirname(__FILE__) . '/Inline.php');

/**
 * ZendR_Sf_Yml_Dumper dumps PHP variables to YAML strings.
 *
 * @package    symfony
 * @subpackage yaml
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: ZendR_Sf_Yml_Dumper.class.php 10575 2008-08-01 13:08:42Z nicolas $
 */
class ZendR_Sf_Yml_Dumper
{

    /**
     * Dumps a PHP value to YAML.
     *
     * @param  mixed   $input  The PHP value
     * @param  integer $inline The level where you switch to inline YAML
     * @param  integer $indent The level o indentation indentation (used internally)
     *
     * @return string  The YAML representation of the PHP value
     */
    public function dump($input, $inline = 0, $indent = 0)
    {
        $output = '';
        $prefix = $indent ? str_repeat(' ', $indent) : '';

        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix . ZendR_Sf_Yml_Inline::dump($input);
        } else {
            $isAHash = array_keys($input) !== range(0, count($input) - 1);

            foreach ($input as $key => $value) {
                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

                $output .= sprintf('%s%s%s%s',
                                $prefix,
                                $isAHash ? ZendR_Sf_Yml_Inline::dump($key) . ':' : '-',
                                $willBeInlined ? ' ' : "\n",
                                $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + 2)
                        ) . ($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }

}
