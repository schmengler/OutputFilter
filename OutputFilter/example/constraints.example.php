<?php
// basic filter
$filter = new HtmlEntitiesFilter();

// only wrap getter methods and don't wrap pseudo private properties (leading underscore). Notice the regular expression syntax
$constraints = array(
	'includeMethods' => '/^get/',
	'excludeProperties' => '/^_/'
);
/*// this has the same effect as:
$constraints = new OutputFilterWrapperConstraints();
$constraints->includeMethods = '/^get/';
$constraints->excludeProperties = '/^_/';
*/

// set constraints in constructor:
$wrapper = new OutputFilterWrapper($filter, $constraints);
$wrapper->wrap($data, $filter, $constraints);