<?php

/**
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 */

class HtmlEntitiesFilter implements Zend_Filter_Interface
{
	public $quoteStyle = ENT_QUOTES;
	public $charset = 'UTF-8';
	public $doubleEncode = true;

	public function filter($value)
	{
		return htmlentities($value, $this->quoteStyle, $this->charset, $this->doubleEncode);
	}
}

?>