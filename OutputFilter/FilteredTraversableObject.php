<?php
class FilteredTraversableObject extends FilteredObject implements IteratorAggregate
{
	public function getIterator()
	{
		if ($this->base instanceof FilteredTraversableObject) {
			return new FilteredTraversable($this->wrapper, $this->base->getIterator());
		}
		return new FilteredTraversable($this->wrapper, $this->base);
	}
}