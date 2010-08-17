<?php
/**
 * @author fs
 * @todo include/exclude definitions optional(!) as arrays; helper methods includeProperty() etc.
 *
 */
class OutputFilterWrapperConstraints
{
	/**
	 * @var string
	 */
	const ALL = '/.*/';
	/**
	 * @var string
	 */
	const NONE = '/[^.]/';
	const PROPERTY = 1;
	const METHOD = 2;
	const ARRAY_KEY = 4;

	/**
	 * @var string
	 */
	public $includeProperties = self::ALL;
	/**
	 * @var string
	 */
	public $excludeProperties = self::NONE;
	/**
	 * @var string
	 */
	public $includeMethods = self::ALL;
	/**
	 * @var string
	 */
	public $excludeMethods = self::NONE;
	/**
	 * @var string
	 */
	public $includeArrayKeys = self::ALL;
	/**
	 * @var string
	 */
	public $excludeArrayKeys = self::NONE;

	public function __construct($constraints = null)
	{
		if ($constraints instanceof OutputFilterWrapperConstraints) {
			$constraints = (array)$constraints;
		}
		if (is_array($constraints)) {
			foreach(get_class_vars(__CLASS__) as $var=>$default) {
				if (isset($constraints[$var])) {
					$this->$var = $constraints[$var];
				}
			}
		}
	}
	/**
	 * @param string $methodName
	 * @return bool
	 */
	public function allowFilterMethod($methodName)
	{
		return self::incexc($methodName, $this->includeMethods, $this->excludeMethods);
	}
	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function allowFilterProperty($propertyName)
	{
		return self::incexc($propertyName, $this->includeProperties, $this->excludeProperties);
	}
	/**
	 * @param string $key
	 * @return bool
	 */
	public function allowFilterArrayKey($key)
	{
		return self::incexc($key, $this->includeArrayKeys, $this->excludeArrayKeys);
	}
	/**
	 * @param int $type
	 * @param string $value
	 * @return bool
	 */
	public function allowFilter($type, $name)
	{
		switch($type) {
			case self::PROPERTY: return $this->allowFilterProperty($name);
			case self::METHOD: return $this->allowFilterMethod($name);
			case self::ARRAY_KEY: return $this->allowFilterArrayKey($name);
			default: throw new InvalidArgumentException('Invalid object type ' . $type);
		}
	}
	/**
	 * @param string $value
	 * @param string $include
	 * @param string $exclude
	 * @return Bool
	 */
	private static function incexc($value, $include, $exclude)
	{
		if ($include===self::NONE || $exclude===self::ALL) {
			return false;
		}
		if ($include!==self::ALL && preg_match($include, $value) === 0) {
			return false;
		}
		if ($exclude!==self::NONE && preg_match($exclude, $value) === 1) {
			return false;
		}
		return true;
	}
}