<?php
class FilteredTraversableArrayObject extends FilteredArrayObject implements ArrayAccess, IteratorAggregate
{
	protected function checkType($base)
	{
		return $base instanceof ArrayAccess && $base instanceof Traversable;
	}
	public function offsetExists($offset)
	{
		return isset($this->base[$offset]);
	}
	public function offsetGet($offset)
	{
		return $this->wrapper->filterRecursive(
			$this->base[$offset],
			OutputFilterWrapperConstraints::ARRAY_KEY,
			$offset
		);
	}
	public function offsetSet($offset, $value)
	{
		$this->base[$offset] = $value;
	}
	public function offsetUnset($offset)
	{
		unset($this->base[$offset]);
	}

	public function getIterator()
	{
		return new FilteredTraversable($this->wrapper, $this->base);
	}
}