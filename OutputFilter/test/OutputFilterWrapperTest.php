<?php

require_once 'SGH-lib\OutputFilter/outputfilter.lib.php';
require_once 'SGH-lib\OutputFilter/Filters/HtmlEntitiesFilter.php';
require_once 'SGH-lib\OutputFilter/Filters/Nl2BrFilter.php';
require_once 'SGH-lib\OutputFilter/Filters/NullFilter.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * OutputFilterWrapper test case.
 */
class OutputFilterWrapperTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var OutputFilterWrapper
	 */
	private $OutputFilterWrapper;
	/**
	 * @var Zend_Filter_Interface
	 */
	private $filter;
	/**
	 * @var string
	 */
	private $testString;
	/**
	 * @var stdClass
	 */
	private $testObject;
	/**
	 * @var array
	 */
	private $testArray, $test2DArray, $test3DArray, $testAssocArray;
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		$this->filter = new HtmlEntitiesFilter();
		$this->OutputFilterWrapper = new OutputFilterWrapper($this->filter);
		$this->testString = '<b>foo & bar :></b>';
		$this->testObject = new stdClass();
		$this->testObject->foo = $this->testString;
		$this->testObject->bar = $this->testString;
		$this->testArray = array($this->testString, $this->testString);
		$this->test2DArray = array($this->testArray, $this->testArray);
		$this->test3DArray = array($this->test2DArray, $this->test2DArray);
		$this->testAssocArray = array('foo'=>$this->testString, 'bar'=>$this->testString);
		parent::setUp ();
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
	
	private function wrap(&$subject) {
		$this->OutputFilterWrapper->wrap($subject);
	}
	private function makeFiltered($subject) {
		return $this->OutputFilterWrapper->filterRecursive($subject);
	}

	/**
	 * Tests OutputFilterWrapper->setConstraints()
	 */
	public function testSetConstraints() {
		
		$constraints = new OutputFilterWrapperConstraints();
		$constraints->includeProperties = '/^foo$/';
		$this->OutputFilterWrapper->setConstraints($constraints);
		$this->assertEquals($constraints, $this->OutputFilterWrapper->getConstraints());
		
	}
	
	/**
	 * Tests OutputFilterWrapper->getConstraints()
	 */
	public function testGetConstraints() {

		$this->OutputFilterWrapper->getConstraints()->includeProperties = '/^foo$/';
		$this->assertEquals('/^foo$/', $this->OutputFilterWrapper->getConstraints()->includeProperties);
	}
	
	public function testConstraints() {
		$this->OutputFilterWrapper->setConstraints(array('includeProperties'=>'/^foo$/'));
		$wrapped = $this->makeFiltered($this->testObject);
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped->foo);
		$this->assertSame($this->testString, $wrapped->bar);
	}
	
	public function testMultipleWrappers() {
		$testString = "\n<b>foo & bar</b>\n\r";
		$this->testObject->xFoo = $testString;
		$this->testObject->yFoo = $testString;
		$this->testObject->xyFoo = $testString;
		
		$innerFilter = new HtmlEntitiesFilter();
		$outerFilter = new Nl2BrFilter();
		$innerWrapper = new OutputFilterWrapper($innerFilter, array('includeProperties'=>'/x/'));
		$outerWrapper = new OutputFilterWrapper($outerFilter, array('includeProperties'=>'/y/'));
		
		$wrapped = $outerWrapper->filterRecursive($innerWrapper->filterRecursive($this->testObject));
		$this->assertSame($innerFilter->filter($testString), (string)$wrapped->xFoo, 'inner filter failed');
		$this->assertSame($outerFilter->filter($testString), (string)$wrapped->yFoo, 'outer filter failed');
		$this->assertSame($outerFilter->filter($innerFilter->filter($testString)), (string)$wrapped->xyFoo, 'combination failed');
	}
	
	public function testMultipleWrappersAsChain() {
		$testString = "\n<b>foo & bar</b>\n\r";
		$this->testObject->xFoo = $testString;
		$this->testObject->yFoo = $testString;
		$this->testObject->xyFoo = $testString;
		
		$innerFilter = new HtmlEntitiesFilter();
		$outerFilter = new Nl2BrFilter();
		$innerWrapper = new OutputFilterWrapper($innerFilter, array('includeProperties'=>'/x/'));
		$outerWrapper = new OutputFilterWrapper($outerFilter, array('includeProperties'=>'/y/'));
		$wrapperChain = new OutputFilterWrapperChain();
		$wrapperChain->pushWrapper($innerWrapper)->pushWrapper($outerWrapper);
		
		$wrapped = $wrapperChain->filterRecursive($this->testObject);
		$this->assertSame($innerFilter->filter($testString), (string)$wrapped->xFoo, 'inner filter failed');
		$this->assertSame($outerFilter->filter($testString), (string)$wrapped->yFoo, 'outer filter failed');
		$this->assertSame($outerFilter->filter($innerFilter->filter($testString)), (string)$wrapped->xyFoo, 'combination failed');
	}
	
	/**
	 * Tests OutputFilterWrapper::wrap()
	 */
	public function testWrap() {
		$testString = $this->testString;
		$this->wrap($testString);
		$this->assertSame($this->filter->filter($this->testString), (string)$testString);
	}
	
	/**
	 * Tests OutputFilterWrapper->unfiltered()
	 */
	public function testUnfilteredString() {
		$wrapped = $this->makeFiltered($this->testString);
		$this->assertSame($this->testString, $wrapped->unfiltered());
	}
	
	/**
	 * Tests OutputFilterWrapper->unfiltered()
	 */
	public function testUnfilteredObject() {
		$wrapped = $this->makeFiltered($this->testObject);
		$this->assertSame($this->testString, $wrapped->unfiltered()->foo);
	}
	
	/**
	 * Tests OutputFilterWrapper->unfiltered()
	 */
	public function testUnfilteredProperty() {
		$wrapped = $this->makeFiltered($this->testObject);
		$this->assertSame($this->testString, $wrapped->foo->unfiltered());
	}
	
	public function testInteger()
	{
		$wrapped = $this->makeFiltered(123);
		$this->assertSame(123, $wrapped);
	}
	
	public function testIntFilterBehaviour()
	{
		// wrap it but use a dummy filter
		$this->OutputFilterWrapper
			->setIntFilterBehaviour(OutputFilterWrapperBehaviour::WRAP())
			->setFilter(new NullFilter());

		$wrapped = $this->makeFiltered(123);
		$this->assertSame('123', (string)$wrapped);
		$this->assertSame(123, $wrapped->scalar());
	}
	
	/**
	 * Tests OutputFilterWrapper->__get()
	 */
	public function testGetProperties() {
		$wrapped = $this->makeFiltered($this->testObject);
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped->foo);
	}

	/**
	 * Tests OutputFilterWrapper->__set()
	 */
	public function testSetProperties() {
		$wrapped = $this->makeFiltered($this->testObject);
		$wrapped->bar = $this->testString;
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped->bar);
	}
	
	/**
	 * Tests OutputFilterWrapper->offsetGet()
	 */
	public function testGetArrayElement() {
		$testArray = array($this->testString);
		$wrapped = $this->makeFiltered($testArray);
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped[0]);
	}

	/**
	 * Tests OutputFilterWrapper->offsetSet()
	 */
	public function testSetArrayElement() {
		$testArray = array();
		$wrapped = $this->makeFiltered($testArray);
		$wrapped[0] = $this->testString;
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped[0]);
	}
	
	/**
	 * Tests Countable interface
	 */
	public function testCountArray() {
		$wrapped = $this->makeFiltered($this->testArray);
		$this->assertSame(2, count($wrapped));
	}

	/**
	 * Tests Iterator Interface
	 */
	public function testIterateArray() {
		$wrapped = $this->makeFiltered($this->testArray);
		$count = 0;
		foreach($wrapped as $item) {
			$this->assertSame($this->filter->filter($this->testString), (string)$item);
			++$count;
		}
		$this->assertSame(2, $count);
	}
	
	public function testIterator() {
		$testIterator = new IteratorIterator(new ArrayIterator($this->testArray));
		$wrapped = $this->makeFiltered($testIterator);
		$count = 0;
		foreach($wrapped as $item) {
			$this->assertSame($this->filter->filter($this->testString), (string)$item);
			++$count;
		}
		$this->assertSame(2, $count);
	}
	
	public function testDoubleFilterIterator() {
		$testIterator = new IteratorIterator(new ArrayIterator($this->testArray));
		$wrapped = $this->makeFiltered($this->makeFiltered($testIterator));
		$count = 0;
		foreach($wrapped as $item) {
			$this->assertSame($this->filter->filter($this->filter->filter($this->testString)), (string)$item);
			++$count;
		}
		$this->assertSame(2, $count);		
	}
	
	public function testTwoDimensionalArray() {
		$wrapped = $this->makeFiltered($this->test2DArray);
		$count = 0;
		foreach($wrapped as $column) {
			$this->assertSame(2, count($column));
			$count2 = 0;
			foreach($column as $item) {
				$this->assertSame($this->filter->filter($this->testString), (string)$item);
				++$count2;
			}
			$this->assertSame(2, $count2);
			++$count;
		}
		$this->assertSame(2, $count);		
	}
	public function testThreeDimensionalArray() {
		$wrapped = $this->makeFiltered($this->test3DArray);
		$count = 0;
		$this->assertType('FilteredArray', $wrapped);
		foreach($wrapped as $k => $column) {
			$this->assertType('FilteredArray', $column);
			$this->assertSame(2, count($column));
			$count2 = 0;
			foreach($column as $l => $group) {
				$this->assertType('FilteredArray', $group);
				$this->assertSame(2, count($group));
				$count3 = 0;
				foreach($group as $m => $item) {
					$this->assertSame($this->filter->filter($this->testString), (string)$item);
					++$count3;
				}
				$this->assertSame(2, $count3);
				++$count2;
			}
			$this->assertSame(2, $count2);
			++$count;
		}
		$this->assertSame(2, $count);		
	}
	public function testIterateAssocArray() {
		$wrapped = $this->makeFiltered($this->testAssocArray);
		$count = 0;
		$this->assertType('FilteredArray', $wrapped);
		reset($this->testAssocArray);
		foreach($wrapped as $k => $item) {
			$this->assertSame(key($this->testAssocArray), $k);
			$this->assertSame($this->filter->filter($this->testString), (string)$item);
			++$count; next($this->testAssocArray);
		}
		$this->assertSame(2, $count);
	}
	
	/**
	 * Tests OutputFilterWrapper->__toString()
	 */
	public function testString() {
		$wrapped = $this->makeFiltered($this->testString);
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped);
	}
}