<?php
class FilteredTraversable extends FilteredAbstract implements Iterator
{
	/**
	 * To make current(), key() etc. also work with IteratorAggregate, the
	 * actual iterator must be handled separately from $base 
	 * 
	 * @var Iterator
	 */
	protected $iterator;
	
	/**
	 * @param mixed $base
	 */
	public function __construct(OutputFilterWrapper $wrapper, $base)
	{
		parent::__construct($wrapper, $base);
		$this->makeIterator();
	}
	
	protected function makeIterator()
	{
		$this->iterator = $this->base;
		while ($this->iterator instanceof IteratorAggregate) {
			$this->iterator = $this->iterator->getIterator();
		}
	}
	
	/**
	 * @param unknown_type $base
	 */
	protected function checkType($base)
	{
		return is_array($base) || $base instanceof Traversable;
	}

	public function current()
	{
		$current = $this->iterator->current();
		if ($current===false) {
			return false;
		}
		// numeric keys should not be constrained!
		$key = $this->iterator->key();
		if (is_numeric($key)) {
			return $this->wrapper->filterRecursive($current);
		}
		return $this->wrapper->filterRecursive(
			$current,
			OutputFilterWrapperConstraints::ARRAY_KEY,
			$key
		);
	}
	public function key()
	{
		return $this->iterator->key();
	}
	public function next()
	{
		$this->iterator->next();
	}
	public function rewind()
	{
		$this->iterator->rewind();
	}
	public function valid()
	{
		return $this->iterator->valid();
	}

}