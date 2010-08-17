<?php
final class OutputFilterWrapperBehaviour extends Enum {
	/**
	 * @return OutputFilterWrapperBehaviour Defined behaviour: return filtered value
	 */
	public static final function FILTER() { return self::___get('OutputFilterWrapperBehaviour', 'FILTER'); }
	/**
	 * @return OutputFilterWrapperBehaviour Defined behaviour: return wrapped value (instance of FilteredScalar)
	 */
	public static final function WRAP() { return self::___get('OutputFilterWrapperBehaviour', 'WRAP'); }
	/**
	 * @return OutputFilterWrapperBehaviour Defined behaviour: don't filter at all
	 */
	public static final function NONE() { return self::___get('OutputFilterWrapperBehaviour', 'NONE'); }
}