<?php
class FilterChain implements Zend_Filter_Interface
{
	protected $filters = array();
	public function pushFilter(Zend_Filter_Interface $filter)
	{
		array_push($this->filters, $filter);
		return $this;
	} 
	public function filter($value)
	{
		foreach($this->filters as $filter)
		{
			$value = $filter->filter($value);
		}
		return $value;
	}
}