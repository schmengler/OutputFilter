<?php
set_include_path(get_include_path(). PATH_SEPARATOR . realpath(dirname(__FILE__).'/../..'));

require_once 'PHPUnit\Framework\TestSuite.php';

require_once 'OutputFilter\test\OutputFilterWrapperConstraintsTest.php';

require_once 'OutputFilter\test\OutputFilterWrapperTest.php';

/**
 * Static test suite.
 */
class OutputFilterTestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'OutputFilterTestSuite' );
		
		$this->addTestSuite ( 'OutputFilterWrapperConstraintsTest' );
		
		$this->addTestSuite ( 'OutputFilterWrapperTest' );
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

