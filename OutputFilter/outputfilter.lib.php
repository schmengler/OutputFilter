<?php
/**
 * Include this file to use the package
 * 
 * @package OutputFilter
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 * @license BSD
 * @link http://creativecommons.org/licenses/BSD/
 * @version 1.0
 */

/**
 * change this to your path of the Typesafe Enum lib
 */
require_once 'Datatypes/TypeSafeEnum/typesafeenum.lib.php';

/**
 * It works perfectly without Zend Framework ;-)
 */
if (!interface_exists('Zend_Filter_Interface'))
{
	interface Zend_Filter_Interface
	{
		public function filter($value); 
	}
}

/**
 * Autoload function
 */
function autoload_outputfilter_lib($className) {
    $filename = dirname(__FILE__) . '/' . $className . '.php';
    if (file_exists($filename)) {
        require_once $filename;
        return true;
    }
    return false;
}

spl_autoload_register('autoload_outputfilter_lib');