<?php
class NullFilter implements Zend_Filter_Interface
{
	public function filter($value)
	{
		return $value;
	}
}