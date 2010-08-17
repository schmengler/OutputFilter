<?php
// A class with a public and private properties, getter and setter methods and a __toString() method
// used by filter.example.php
class ExampleData
{
	private
		$priv = '<b>foo</b>',
		$array = array();
	public
		$pub = '<b>bar</b>';
	
	public function __construct($priv=null,$pub=null)
	{
		if ($priv!==null) $this->priv = $priv;
		if ($pub!==null) $this->pub = $pub;
	}
	
	public function __toString()
	{

		return sprintf("[priv: %s, pub: %s, array: [%s]]", $this->priv, $this->pub, join(',', $this->array)); 
	}
	
	public function getPriv()
	{
		return $this->priv;
	}
	
	public function setPriv($priv)
	{
		$this->priv = $priv;
		return $this;
	}
	
	public function getArray()
	{
		return $this->array;
	}
	public function pushArray($item)
	{
		$this->array[] = $item;
		return $this;
	}
}

// now we make a complex structure:
$_proto = new ExampleData();
$exampleData = new ExampleData(null, clone $_proto);
$exampleData->pushArray(clone $_proto)
	->pushArray(clone $_proto)
	->setPriv(clone $exampleData)
//	->pushArray(clone $exampleData)
;
unset($_proto);
return $exampleData;