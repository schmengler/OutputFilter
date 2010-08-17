<?php
abstract class FilteredAbstract
{
	/**
	 * @var array|object|scalar|null
	 */
	protected $base;
	
	/**
	 * @var OutputFilterWrapper
	 */
	protected $wrapper;
	
	/**
	 * @param mixed $base
	 */
	public function __construct(OutputFilterWrapper $wrapper, $base)
	{
		$this->wrapper = $wrapper;
		$this->setBase($base);
	}
	
	protected function setBase($base)
	{
		if (!$this->checkType($base)) {
			throw new InvalidArgumentException('base of type ' . gettype($base) . ' cannot be filtered as ' . get_class($this));
		}
		$this->base = $base;
	}
	
	abstract protected function checkType($base);
	
	public function unfiltered()
	{
		return $this->base;
	}
	
	/**
	 * Recursive filter by default
	 * 
	 * @param mixed $value
	 */
	protected function filter($value)
	{
		return $this->wrapper->filterRecursive($value);
	}
}