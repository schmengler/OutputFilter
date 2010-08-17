<?php
/**
 * Wrapped scalar, accessible through __toString()
 *
 */
class FilteredScalar extends FilteredAbstract
{
	/**
	 * Override recursive filter method!
	 * 
	 * @param mixed $value
	 */
	protected function filter($value)
	{
		return $this->wrapper->getFilter()->filter($value);
	}
	protected function checkType($base)
	{
		return is_scalar($base);
	}
	public function __toString()
	{
		return (string)$this->filter($this->base);
	}
	/**
	 * @return type safe filtered value (i.e. bool, integer)
	 */
	public function scalar()
	{
		return $this->filter($this->base);
	}
}