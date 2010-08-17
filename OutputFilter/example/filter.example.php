<?php
require_once dirname(__FILE__) . '/../outputfilter.lib.php';
require_once dirname(__FILE__) . '/../Filters/HtmlEntitiesFilter.php';
require_once dirname(__FILE__) . '/../Filters/Nl2BrFilter.php';

// some complex data structure ;-)
$data = include(dirname(__FILE__) . '/data.example.php');

// some filter (must be an instance of Zend_Filter_Interface!)
// - The package comes with HtmlEntitiesFilter and Nl2BrFilter
// - also possible: chaining multiple filters:
//$filter = new OutputFilterChain();
//$filter->pushFilter(new HtmlEntitiesFilter())
//       ->pushFilter(new Nl2BrFilter());
$filter = new HtmlEntitiesFilter();

$wrapper = new OutputFilterWrapper($filter);

// black magic!
$wrapper->wrap($data);

// demonstration, execute this file to see how it works!
function filter_example($code)
{
	global $data;
	printf("<br>\n<b>%s</b><br>\n", $code);
	eval('echo ' . $code . ';');
}
filter_example('$data');
filter_example('$data->pub->getPriv()');
filter_example('$data->getPriv()->pub->pub');
filter_example('$data->unfiltered()->getPriv()->getPriv()');
filter_example('$data->getPriv()->getPriv()->unfiltered()');
foreach($data->getArray() as $key=>$item) {
	echo "<br>\n<b>\$data->getArray()[$key]</b><br>\n";
	echo $item;
	echo "<br>\n<b>\$data->getArray()[$key]->pub</b><br>\n";
	echo $item->pub;
}
filter_example('$data->setPriv(\'<i>changed!</i>\')');
filter_example('$data->getPriv()');


