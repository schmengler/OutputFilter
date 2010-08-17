+-----------------------------------------------------------------------------+
|                             OutputFilter                                    |
+-----------------------------------------------------------------------------+

- Synopsis
- Requirements
- Files
- Simple Usage
- Extended Usage: Wrapper Constraints
- Extended Usage: Wrapper Behaviour
- Extended Usage: Nested Wrappers
- Extended Usage: Integrations
- Technical Remarks

Synopsis
--------
Often you have to call some filter function like htmlspecialchars() to all of
your output data (or at least to most of it). With OutputFilter this can be done
all at once. It can filter a whole object, that is all of its properties and
even all method results like from getter methods. It can also filter all
elements of an array and all this is done recursively. In fact it acts as a
wrapper that wraps the original object/array and replaces it. Should you need
the unfiltered value, just call unfiltered() on it.

There are integrations for Zend_View and Smarty where everything gets wrapped
automatically. 


Requirements
------------
The package requires PHP 5.2.5 or later because it relies on the Typesafe Enum
package (http://www.phpclasses.org/package/6021).

To use the package, just include outputfilter.lib.php (also edit that file to
include the Typesafe Enum package properly).


Files
-----
readme.txt - the file you are reading right now
license.txt - BSD license
outputfilter.lib.php - library loader, include this file to use the package
OutputFilterWrapper.php - class file: main class
OutputFilterWrapperInterface.php - class file: interface
OutputFilterWrapperConstraints - class file: filter contraints
OutputFilterWrapperBehaviour - class file: filter behaviour on scalars
OutputFilterWrapperChain - class file: class for nested wrappers
FilteredAbstract.php - class file: abstract class for filtered variables
FilteredIterator.php - class file: abstract class for filtered iterators
FilteredObject.php - class file: class for filtered objects
FilteredArray.php - class file: class for filtered arrays
FilteredArrayObject.php - class file: class for filtered objects with ArrayAccess
FilteredScalar.php - class file: class for filtered scalars
example/filter.example.php - executable example
example/data.example.php - data for filter.example.php
Filters/FilterChain.php - class file: filter that combines multiple filters
Filters/HtmlEntitiesFilter.php - class file: htmlentities() filter
Filters/Nl2BrFilter.php - class file: nl2br() filter
Filters/NullFilter.php - class file: dummy filter
Integrations/Smarty/prefilter.wrapper.php - smarty plugin
Integrations/ZendView/FilteredZendView.php - Zend_View extension
test/OutputFilterTestSuite.php - PHPUnit test suite
test/OutputFilterWrapperTest.php - PHPUnit test case
test/OutputFilterWrapperConstraintsTest.php - PHPUnit test case


Simple Usage
------------

First initialize the wrapper with any filter. The package brings some useful
filter classes (HtmlEntitiesFilter, Nl2BrFilter, FilterChain) but you can easily
implement your own filters or use filters from the Zend_Filter package.

	$wrapper = new OutputFilterWrapper(new HtmlEntitiesFilter);

Then you can use it like this:

	$wrapper->wrap($var1);
	$wrapper->wrap($var2);
	$wrapper->wrap($var3);
	...

$var may be anything, from a simple scalar value to arrays and objects to even
resources (the latter being untouched). But the wrapper shows its strengths when
used with complex object and array structures.
In general the wrapped variables can be used just like the originals, but every
string values are returned filtered. For example, if $var1 was an object with
the respective properties and methods, the following is possible and will output
filtered strings:

	echo $var1->getFoo();
	echo $var1->foo;
	echo $var1->fooArray[0];
	echo $var1->fooObject->bar;

If you need single values unfiltered, no problem:

	echo $var1->foo->unfiltered();

If you have to compare filtered values, they should be converted to strings
explicitly (see also: Extended Usage: Wrapper Behaviour), for example:

	echo ((string)$var1->foo) === '') ? 'empty' : $var1->foo;

The same goes for usage of string functions like:

	strlen((string)$var1->foo)


- Extended Usage: Wrapper Constraints
-------------------------------------

You can constrain the wrapper to include or exclude certain methods, properties
and array keys from being filtered. You can modify these constraints via the
getConstraints() method like that:

	$wrapper->getConstraints()->includeProperties = '/.../';
	$wrapper->getConstraints()->excludeProperties = '/.../';
	$wrapper->getConstraints()->includeMethods = '/.../';
	$wrapper->getConstraints()->excludeMethods = '/.../';
	$wrapper->getConstraints()->includeArrayKeys = '/.../';
	$wrapper->getConstraints()->excludeArrayKeys = '/.../';

or set them via constructor:

	$constraints = array(
		'includeProperties' => '/.../',
		'excludeProperties' => '/.../',
		'includeMethods' => '/.../',
		'excludeMethods' =>'/.../',
		'includeArrayKeys' =>'/.../',
		'excludeArrayKeys' =>'/.../',
	);
	$wrapper = new OutputFilterWrapper($filter, $constraints);

Insert any regular expression instead of '/.../'.  

Constraints have the following priority:

- If the include-pattern is set and does not match, no filtering happens
- If the include-pattern is set and matches but the exclude-pattern also is set
and matches, no filtering happens
- If the include-pattern is not set but the exclude-pattern is set and matches,
no filtering happens.
- All other cases: Filtering happens :)


Extended Usage: Wrapper Behaviour
---------------------------------

By default, the wrapper handles scalars as follows: strings are being wrapped
into FilteredScalar objects, whose __toString() method returns the filtered
value, whereas integers, booleans and null values are not filtered at all.
For all of these types you can specify the behaviour individually via the
following methods of OutputFilterWrapper:

setStringFilterBehaviour($behaviour)
setIntFilterBehaviour($behaviour)
setBoolFilterBehaviour($behaviour)
setNullFilterBehaviour($behaviour)

where the $behaviour parameter has one of the following values:

OutputFilterWrapperBehaviour::FILTER()
OutputFilterWrapperBehaviour::WRAP()
OutputFilterWrapperBehaviour::NONE()

notice the parantheses issued from the TypeSafeEnum package! In detail:
FILTER: the value will be filtered directly
WRAP: the value will be wrapped into a FilteredScalar object (see above)
NONE: the value will be returned unchanged


Extended Usage: Nested Wrappers
-------------------------------

It is possible to use multiple wrappers on a variable. This might sound useless
because if you just want to use multiple filters in a row, you can chain them
with the FilterChain class:
	
	$filter = new FilterChain;
	$filter->pushFilter(new HtmlEntitiesFilter)
		->pushFilter(new Nl2BrFilter);

But it makes sense as soon as the wrappers use different constraints! Let's say
you pass an array of variables to your view script and all keys starting with
'bb' are BBCode that should be translated to HTML (You have written a filter
class BBCodeFilter for this purpose)

	$standardWrapper = new OutputFilterWrapper(new HtmlEntitiesFilter);
	$bbCodeWrapper = new OutputFilterWrapper(new BBCodeFilter);
	$bbCodeWrapper->getConstraints()->includeArrayKeys = '/^bb/';
	$bbCodeWrapper->getConstraints()->includeProperties = OutputFilterWrapperConstraints::NONE;
	$bbCodeWrapper->getConstraints()->includeMethods = OutputFilterWrapperConstraints::NONE;

	$bbCodeWrapper->wrap($vars);
	$standardWrapper->wrap($vars);

Now first every special characters get converted to html entities and then, if
the array key started with 'bb', the BBCode gets translated to HTML. This order
is important; notice that the wrapping order is the other way around, the first
(innermost) wrapper gets called last when it comes to filtering.

If you want to use the same nesting repeatedly, you can do it easily with the
OutputFilterWrapperChain class:

	$nestedWrapper = new OutputFilterWrapperChain;
	$nestedWrapper->pushWrapper($bbCodeWrapper)
		->pushWrapper($standardWrapper);

	$nestedWrapper->wrap($vars);
	// ...

This is especially useful for the framework integrations (see next section).


Extended Usage: Integrations
----------------------------

The package is easily integrable into existing frameworks, two examples are
supplied in the Integrations directory.

Smarty: just put prefilter.wrapper.php in your plugins folder and include the
library loader (outputfilter.lib.php) anywhere. By default, all smarty variables
are filtered with HtmlEntitiesFilter, for customization see the comment section
of the plugin file.

Zend Framework (Zend_View): in your view initalization replace Zend_View 
with FilteredZendView which is a direct descendant of Zend_View and assign a
wrapper with $view->setOutputFilterWrapper($wrapper);

Both implementations rely on a single wrapper instance, but this may also be a
OutputFilterWrapperChain instance with nested wrappers.

 
Technical Remarks
-----------------

__1.__ unfiltered() __

	echo $var1->foo->unfiltered();

is equivalent to:

	echo $var1->unfiltered()->foo;
	
Calling unfiltered() at a higher level has the same result but is a bit faster!


__2.__ wrap() and filterRecursive() __

	$wrapper->wrap($var)

is a shortcut for:

	$var = $wrapper->filterRecursive($var);

If you do not want to replace the original variable, use filterRecursive!


__3.__ filter method __

The filtering always happens on demand, not on wrapping. Only exception is the
direct wrapping of a scalar value if the respective behaviour is set to FILTER.


__4.__ special objects __

Wrapped instances of ArrayAccess or Iterator will retain these interfaces and
thus still can be used accordingly.

__5.__ scalar() __

If you have a filter that returns not only strings and you need to get the
filtered variable with its exact type, you can use the scalar() method of
FilteredScalar:

	$var->foo->scalar()

it does the same as the __toString() method but without casting the filtered
value to a string. Unlike unfiltered() this only works on filtered scalars not
on complex variables (i.e. $var->scalar()->foo is not possible - remember that
the real filtering only happens at the end, on scalar values)