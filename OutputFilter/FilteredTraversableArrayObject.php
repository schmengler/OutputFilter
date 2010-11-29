<?php
/**
 * also Countable, important for wrapping FilteredArrayObject (multiwrap arrays) 
 * 
 * @author fs
 *
 */
class FilteredTraversableArrayObject extends FilteredArrayObject implements ArrayAccess, IteratorAggregate, Countable
{
	protected function checkType($base)
	{
		return $base instanceof ArrayAccess && $base instanceof Traversable;
	}

	public function getIterator()
	{
		return new FilteredTraversable($this->wrapper, $this->base);
	}
	/**
	 * 
	 */
	public function count()
	{
		if ($this->base instanceof Countable) {
			return count($this->base);
		}
		$count = 0;
		foreach($this->base as $dummy) {
			++$count;
		}
		return $count;
	}

}