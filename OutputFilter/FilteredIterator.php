<?php
abstract class FilteredIterator extends FilteredAbstract implements Iterator
{
	public function current()
	{
		$current = current($this->base);
		return $current===false ? false : $this->wrapper->filterRecursive(
			$current,
			OutputFilterWrapperConstraints::ARRAY_KEY,
			key($this->base)
		);
	}
	public function key()
	{
		return key($this->base);
	}
	public function next()
	{
		next($this->base);
	}
	public function rewind()
	{
		reset($this->base);
	}
	public function valid()
	{
		return current($this->base)!==false;
	}
	
}