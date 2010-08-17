<?php
interface OutputFilterWrapperInterface
{
	/**
	 * @param mixed $subject
	 */
	public function wrap(&$subject);
	/**
	 * @param mixed $value
	 * @param int $type {OutputFilterWrapperConstraints::METHOD, OutputFilterWrapperConstraints::PROPERTY, OutputFilterWrapperConstraints::ARRAY_KEY}
	 * @param string $name
	 */
	public function filterRecursive($value, $type = null, $name = null);
}