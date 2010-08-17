<?php
if (!interface_exists('Zend_Filter_Interface'))
{
	interface Zend_Filter_Interface
	{
		public function filter($value); 
	}
}

SGH_lib_import('Datatypes.TypeSafeEnum');