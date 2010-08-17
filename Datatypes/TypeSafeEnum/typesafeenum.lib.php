<?php

/**
 * Include this file to use the package
 * 
 * @package TypesafeEnum
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 * @license BSD
 * @link http://creativecommons.org/licenses/BSD/
 * @version 1.1
 */

if (version_compare(PHP_VERSION, '5.2.5', '<')) {
	$_line = __LINE__ + 1;
	/*
	 * functionality from PHP > 5.0:
	 *
	 * 5.2.5: debug_backtrace() parameter in Enum::define() and Enum::___get()
	 * 5.2.0: recursive mkdir in Enum::___writeToCache()
	 * 5.1.0: InvalidArgumentException from SPL in Enum::define() and Enum::valuesOf()
	 * 5.1.0: LOCK_EX flag in Enum::___writeToCache()
	 * 5.1.0: array_intersect_key in array_iunique
	 * 5.0.3: is_subclass_of in define_once and valuesOf
	 *
	 * If you remove the respective optional parameters from debug_backtrace and mkdir
	 * and don't set Enum::$cacheReUsePackagePath, the package works fine on PHP 5.1.0.
	 * Workarounds to get the package running on PHP 5.0 should also be possible but not
	 * recommended.
	 *
	 */
	throw new Exception('The Typesafe Enumeration package needs PHP 5.2.5 or higher. See ' .
		__FILE__ . ' Line ' . $_line . ' for information about older versions.');
}

if (!function_exists('array_iunique')) {
	/**
	 * Case insensitive array unique
	 * 
	 * @param array $array
	 * @return array
	 */
	function array_iunique($array) {
	    return array_intersect_key($array,array_unique(
	                 array_map('strtolower',$array)));
	}
}

/**
 * the class
 */
require_once dirname(__FILE__) . '/Enum.php';

?>