<?php
class OutputFilterWrapperChain implements OutputFilterWrapperInterface
{
	/**
	 * @var OutputFilterWrapperInterface[]
	 */
	protected $wrappers = array();
	/**
	 * @param OutputFilterWrapperInterface $wrapper
	 * @return OutputFilterWrapperChain
	 */
	public function pushWrapper(OutputFilterWrapperInterface $wrapper)
	{
		array_push($this->wrappers, $wrapper);
		return $this;
	}
	public function prependWrapper(OutputFilterWrapperInterface $wrapper)
	{
		array_unshift($this->wrappers, $wrapper);
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
		foreach($this->wrappers as $wrapper)
		{
			$value = $wrapper->filterRecursive($value);
		}
		return $value;
	}
}