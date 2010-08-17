<?php
/**
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 */

class Nl2BrFilter implements Zend_Filter_Interface
{
	public $isXhtml = true; 
	public function filter($value)
	{
		if (version_compare(PHP_VERSION, '5.3', '>=')) {
			return nl2br($value, $this->isXhtml);
		} else {
			return nl2br($value);
		}
	}
}

?>