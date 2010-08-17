<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     prefilter.wrapper.php
 * Type:     prefilter
 * Name:     wrapper
 * Purpose:  Use OutputFilterWrapper to filter all template vars
 * -------------------------------------------------------------
 * 
 * Usage:
 * =============================================================
 * Per default, the plugin uses the HtmlEntitiesFilter (i.e.
 * htmlentities($var, ENT_QUOTES, 'UTF-8', true)) on all
 * template variables recursively.
 * 
 * To change this behaviour, you can assign another filter
 * and/or constraint rules in your application like this:
 * 
 * $smarty->assign('wrapper_filter', $someFilter);
 * $smarty->assign('wrapper_constraints', $someConstraints);
 * 
 * where $someFilter is a Zend_Filter_Interface instance and
 * $someConstraints an OutputFilterWrapperConstraints object or
 * an array defining some constraint properties. See package
 * documentation for details about constraints and filters!
 * 
 * Another possibility is to assign a complete wrapper object:
 * 
 * $smarty->assign('wrapper', $someWrapperInstance);
 * 
 * this way everything can be configured beforehand.
 * Note: wrapper_filter and wrapper_constraints are ignored then
 * =============================================================
 * 
 */
function smarty_prefilter_wrapper($source, &$smarty)
{
	$wrapper = $smarty->get_template_vars('wrapper');
	if (!$wrapper instanceof OutputFilterWrapperInterface) {
		$filter = $smarty->get_template_vars('wrapper_filter');
		if (!$filter instanceof Zend_Filter_Interface) {
			$filter = new HtmlEntitiesFilter();
		}
		$constraints = $smarty->get_template_vars('wrapper_constraints');
		$wrapper = new OutputFilterWrapper($filter, $constraints);
	}

	// smarty::get_template_vars returns variables by reference
	$vars =& $smarty->get_template_vars();
	$wrapper->wrap($vars);

	// $source stays untouched
	return $source;
}