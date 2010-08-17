<?php
class FilteredObject extends FilteredAbstract
{
	protected function checkType($base)
	{
		return is_object($base);
	}
	public function __get($property)
	{
		return $this->wrapper->filterRecursive(
			$this->base->$property,
			OutputFilterWrapperConstraints::PROPERTY,
			$property
		);
	}

	public function __set($property, $value)
	{
		$this->base->$property = $value;
	}

	public function __call($method, $args)
	{
		return $this->wrapper->filterRecursive(
			call_user_func_array(array($this->base,$method), $args),
			OutputFilterWrapperConstraints::METHOD,
			$method
		);
	}
	
	public function __toString()
	{
		// filters output of $this->base->__toString()
		return (string)$this->wrapper->filterRecursive((string)$this->base);
	}
	
}