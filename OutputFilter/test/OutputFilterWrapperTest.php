<?php

require_once 'IO/Filters/OutputFilter/outputfilter.lib.php';
require_once 'IO/Filters/OutputFilter/Filters/HtmlEntitiesFilter.php';
require_once 'IO/Filters/OutputFilter/Filters/Nl2BrFilter.php';
require_once 'IO/Filters/OutputFilter/Filters/NullFilter.php';

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
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		$this->filter = new HtmlEntitiesFilter();
		$this->OutputFilterWrapper = new OutputFilterWrapper($this->filter);
		$this->testString = '<b>foo & bar :></b>';
		$this->testObject = new stdClass();
		$this->testObject->foo = $this->testString;
		$this->testObject->bar = $this->testString;
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
	 * Tests Iterator Interface
	 */
	public function testIterateArray() {
		$testArray = array($this->testString, $this->testString);
		$wrapped = $this->makeFiltered($testArray);
		//list($key,$item) = each($wrapped);
		foreach($wrapped as $item)
			$this->assertSame($this->filter->filter($this->testString), (string)$item);
	}
	
	/**
	 * Tests OutputFilterWrapper->__toString()
	 */
	public function testString() {
		$wrapped = $this->makeFiltered($this->testString);
		$this->assertSame($this->filter->filter($this->testString), (string)$wrapped);
	}
}

