<?php

require_once 'IO\Filters\OutputFilter\OutputFilterWrapperConstraints.php';

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * OutputFilterWrapperConstraints test case.
 */
class OutputFilterWrapperConstraintsTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var OutputFilterWrapperConstraints
	 */
	private $OutputFilterWrapperConstraints;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		$this->OutputFilterWrapperConstraints = new OutputFilterWrapperConstraints(array(
			'includeProperties'=>'/a/',
			'excludeProperties'=>'/b/',
			'includeMethods'=>'/c/',
			'excludeMethods'=>'/d/',
			'includeArrayKeys'=>'/e/',
			'excludeArrayKeys'=>'/f/',
		));

	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated OutputFilterWrapperConstraintsTest::tearDown()
		

		$this->OutputFilterWrapperConstraints = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
	
	/**
	 * Tests OutputFilterWrapperConstraints->__construct()
	 */
	public function test__constructOnArray() {
		$this->assertEquals('/a/', $this->OutputFilterWrapperConstraints->includeProperties);
		$this->assertEquals('/b/', $this->OutputFilterWrapperConstraints->excludeProperties);
		$this->assertEquals('/c/', $this->OutputFilterWrapperConstraints->includeMethods);
		$this->assertEquals('/d/', $this->OutputFilterWrapperConstraints->excludeMethods);
		$this->assertEquals('/e/', $this->OutputFilterWrapperConstraints->includeArrayKeys);
		$this->assertEquals('/f/', $this->OutputFilterWrapperConstraints->excludeArrayKeys);
	}
	
	/**
	 * Tests OutputFilterWrapperConstraints->__construct()
	 */
	public function test__constructOnObject() {
		$c = new OutputFilterWrapperConstraints($this->OutputFilterWrapperConstraints);
		$this->assertEquals('/a/', $c->includeProperties);
		$this->assertEquals('/b/', $c->excludeProperties);
		$this->assertEquals('/c/', $c->includeMethods);
		$this->assertEquals('/d/', $c->excludeMethods);
		$this->assertEquals('/e/', $c->includeArrayKeys);
		$this->assertEquals('/f/', $c->excludeArrayKeys);
	}
	
	/**
	 * Tests OutputFilterWrapperConstraints->allowFilterMethod()
	 */
	public function testAllowFilterMethod() {
		$this->assertTrue($this->OutputFilterWrapperConstraints->allowFilterMethod('cx'), 'include failed');
		$this->assertFalse($this->OutputFilterWrapperConstraints->allowFilterMethod('cd'), 'exclude failed');
		$this->assertFalse($this->OutputFilterWrapperConstraints->allowFilterMethod('xx'), '!include failed');
	}
	
	/**
	 * Tests OutputFilterWrapperConstraints->allowFilterProperty()
	 */
	public function testAllowFilterProperty() {
		$this->assertTrue($this->OutputFilterWrapperConstraints->allowFilterProperty('ax'), 'include failed');
		$this->assertFalse($this->OutputFilterWrapperConstraints->allowFilterProperty('ab'), 'exclude failed');
		$this->assertFalse($this->OutputFilterWrapperConstraints->allowFilterProperty('xx'), '!include failed');
	}
	
	/**
	 * Tests OutputFilterWrapperConstraints->allowFilterArrayKey()
	 */
	public function testAllowFilterArrayKey() {
		$this->assertTrue($this->OutputFilterWrapperConstraints->allowFilterArrayKey('ex'), 'include failed');
		$this->assertFalse($this->OutputFilterWrapperConstraints->allowFilterArrayKey('ef'), 'exclude failed');
		$this->assertFalse($this->OutputFilterWrapperConstraints->allowFilterArrayKey('xx'), '!include failed');
	}
}

