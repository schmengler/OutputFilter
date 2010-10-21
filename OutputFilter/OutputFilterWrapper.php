<?php
/**
 * Filter scalars, arrays, data objects recursively
 * 
 * Often you have to call some filter function like htmlspecialchars() to all of
 * your output data (or at least to most of it). With OutputFilter this can be
 * done all at once. It can filter a whole object, that is all of its properties
 * and even all method results like from getter methods. It can also filter
 * all elements of an array and all this is done recursively. In fact it acts as
 * a wrapper that wraps the original object/array and replaces it. Should you
 * need the unfiltered value, just call unfiltered() on it.
 * 
 * There are integrations for Zend_View and Smarty where everything gets wrapped
 * automatically. 
 * 
 * See readme.txt or examples for more information.
 *  
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 * @license BSD
 * @link http://creativecommons.org/licenses/BSD/ 
 * @todo allow callbacks as filters?
 */

/**
 * default filter
 */
require_once dirname(__FILE__) . '/Filters/NullFilter.php';

/**
 * Wrapper class for all kind of variables
 * 
 * @package OutputFilter
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 * @version 1.0
 * @access public 
 */
class OutputFilterWrapper implements OutputFilterWrapperInterface
{
	/**
	 * @var OutputFilterWrapperConstraints
	 */
	private $constraints;

	/**
	 * @var OutputFilterWrapperBehaviour
	 */
	private $stringFilterBehaviour;
	
	/**
	 * @var OutputFilterWrapperBehaviour
	 */
	private $intFilterBehaviour;

	/**
	 * @var OutputFilterWrapperBehaviour
	 */
	private $doubleFilterBehaviour;
	
	/**
	 * @var OutputFilterWrapperBehaviour
	 */
	private $boolFilterBehaviour;
	
	/**
	 * @var OutputFilterWrapperBehaviour
	 */
	private $nullFilterBehaviour;
	
	/**
	 * @var Zend_Filter_Interface
	 */
	private $filter;

	/**
	 * @param Zend_Filter_Interface $filter
	 * @param OutputFilterWrapperConstraints|array|null $constraints
	 */
	public function __construct(Zend_Filter_Interface $filter = null, $constraints = null)
	{
		if ($filter === null) {
			$filter = new NullFilter();
		}
		$this->setFilter($filter)
			->setConstraints($constraints)
			->setStringFilterBehaviour(OutputFilterWrapperBehaviour::WRAP())
			->setIntFilterBehaviour(OutputFilterWrapperBehaviour::NONE())
			->setDoubleFilterBehaviour(OutputFilterWrapperBehaviour::NONE())
			->setBoolFilterBehaviour(OutputFilterWrapperBehaviour::NONE())
			->setNullFilterBehaviour(OutputFilterWrapperBehaviour::NONE());
	}
	
	/**
	 * @return OutputFilterWrapperConstraints
	 */
	public function getConstraints()
	{
		return $this->constraints;
	}
	/**
	 * @param OutputFilterWrapperConstraints|array|null $constraints
	 * @return OutputFilterWrapper
	 */
	public function setConstraints($constraints = null)
	{
		$this->constraints = new OutputFilterWrapperConstraints($constraints);
		return $this;
	}
	
	/**
	 * @return Zend_Filter_Interface
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @param Zend_Filter_Interface $filter
	 * @return OutputFilterWrapper
	 */
	public function setFilter(Zend_Filter_Interface $filter) {
		$this->filter = $filter;
		return $this;
	}

	/**
	 * @return OutputFilterWrapperBehaviour
	 */
	public function getStringFilterBehaviour() {
		return $this->stringFilterBehaviour;
	}

	/**
	 * @param OutputFilterWrapperBehaviour
	 * @return OutputFilterWrapper
	 */
	public function setStringFilterBehaviour(OutputFilterWrapperBehaviour $stringFilterBehaviour) {
		$this->stringFilterBehaviour = $stringFilterBehaviour;
		return $this;
	}

	/**
	 * @return OutputFilterWrapperBehaviour
	 */
	public function getIntFilterBehaviour() {
		return $this->intFilterBehaviour;
	}

	/**
	 * @param OutputFilterWrapperBehaviour
	 * @return OutputFilterWrapper
	 */
	public function setIntFilterBehaviour(OutputFilterWrapperBehaviour $intFilterBehaviour) {
		$this->intFilterBehaviour = $intFilterBehaviour;
		return $this;
	}

	/**
	 * @return OutputFilterWrapperBehaviour
	 */
	public function getDoubleFilterBehaviour() {
		return $this->doubleFilterBehaviour;
	}

	/**
	 * @param OutputFilterWrapperBehaviour
	 * @return OutputFilterWrapper
	 */
	public function setDoubleFilterBehaviour(OutputFilterWrapperBehaviour $doubleFilterBehaviour) {
		$this->doubleFilterBehaviour = $doubleFilterBehaviour;
		return $this;
	}

	/**
	 * @return OutputFilterWrapperBehaviour
	 */
	public function getBoolFilterBehaviour() {
		return $this->boolFilterBehaviour;
	}

	/**
	 * @param OutputFilterWrapperBehaviour
	 * @return OutputFilterWrapper
	 */
	public function setBoolFilterBehaviour(OutputFilterWrapperBehaviour $boolFilterBehaviour) {
		$this->boolFilterBehaviour = $boolFilterBehaviour;
		return $this;
	}
	
	/**
	 * @return OutputFilterWrapperBehaviour
	 */
	public function getNullFilterBehaviour() {
		return $this->nullFilterBehaviour;
	}

	/**
	 * @param OutputFilterWrapperBehaviour
	 * @return OutputFilterWrapper
	 */
	public function setNullFilterBehaviour(OutputFilterWrapperBehaviour $nullFilterBehaviour) {
		$this->nullFilterBehaviour = $nullFilterBehaviour;
		return $this;
	}
	
	/**
	 * @param mixed $subject
	 */
	public function wrap(&$subject)
	{
		$subject = $this->filterRecursive($subject);
	}
	
	/**
	 * @param mixed $value
	 * @param int $type {OutputFilterWrapperConstraints::METHOD, OutputFilterWrapperConstraints::PROPERTY, OutputFilterWrapperConstraints::ARRAY_KEY}
	 * @param string $name
	 */
	public function filterRecursive($value, $type = null, $name = null)
	{
		// $type, $name given => check constraints:
		if ($type!==null && false===$this->constraints->allowFilter($type, $name)) {
			return $value;
		}
		if (is_array($value)) {
			return new FilteredArray($this, $value);
		} elseif ($value instanceof ArrayAccess) {
			return new FilteredArrayObject($this, $value);
		} elseif (is_object($value)) {
			return new FilteredObject($this, $value);
		} else {
			return $this->filterValue($value);
		}
	}
	private function filterValue($value)
	{
		$behaviourMap = array(
			'NULL'    => $this->nullFilterBehaviour,
			'string'  => $this->stringFilterBehaviour,
			'integer' => $this->intFilterBehaviour,
			'double'  => $this->doubleFilterBehaviour,
			'boolean' => $this->boolFilterBehaviour,
			'resource' => OutputFilterWrapperBehaviour::NONE(),
		);
		switch($behaviourMap[gettype($value)])
		{
			case OutputFilterWrapperBehaviour::FILTER():
				return $this->filter->filter($value);
			case OutputFilterWrapperBehaviour::WRAP():
				return new FilteredScalar($this, $value);
			default: case OutputFilterWrapperBehaviour::NONE():
				return $value;
		}
	}

}

?>