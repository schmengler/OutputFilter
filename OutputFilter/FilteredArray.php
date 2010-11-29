<?php
class FilteredArray extends FilteredTraversable implements ArrayAccess, Countable
{
	protected function makeIterator()
	{
		$this->iterator = new ArrayIterator($this->base);
	}
	protected function checkType($base)
	{
		return is_array($base);
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
		if ($offset === null) {
			$this->base[] = $value;
		} else {
			$this->base[$offset] = $value;
		}
	}
	public function offsetUnset($offset)
	{
		unset($this->base[$offset]);
	}
	
	public function toArray()
	{
		$array = array();
		foreach(array_keys($this->base) as $key) {
			$array[$key] = $this[$key];
		}
		return $array;
	}
	/**
	 * 
	 */
	public function count()
	{
		return count($this->base);
	}

}