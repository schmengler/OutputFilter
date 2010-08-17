<?php

/**
 * Typesafe enumeration pattern adapted and abstracted for PHP
 * 
 * An enumeration class can be easily generated with
 * <code>Enum::define('Name', 'VALUE1', 'VALUE2', ...);</code>
 * 
 * Then you can use Name::VALUE1() etc. which are instances of Name.
 * Note that these are methods, not constants or static properties because
 * these cannot be initalized with objects. However, the methods
 * always return the same instance each that cannot be cloned or altered.
 * 
 * See class documentation or examples for more information
 * 
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 * @license BSD
 * @link http://creativecommons.org/licenses/BSD/
 * @link http://java.sun.com/developer/Books/shiftintojava/page1.html#replaceenums
 * @version 1.1
 * @todo allow absolute path for cache directory!
 */

/**
 * Enum base class and generator
 * 
 * Typical Usage:
 * <code>
 * Enum::define('MyEnum', 'Value1', 'Value2', 'Value3', 'Value4');
 * 
 * function doSomething(MyEnum $myenum) {
 * 	echo (string) $myEnum;
 * 	switch ($myenum) {
 * 		case MyEnum::Value1():
 * 			// do something for Value1
 * 			break;
 * 		case MyEnum::Value2():
 * 			// do something for Value2
 * 			break;
 * 		case MyEnum::Value3():
 * 			// do something for Value3
 * 			break;
 * 		case MyEnum::Value4():
 * 			// do something for Value4
 * 			break;
 * 	}
 * }
 * doSomething(MyEnum::Value1());
 * </code>
 * 
 * @package TypesafeEnum
 * @author Fabian Schmengler <fschmengler@sgh-it.eu>
 * @copyright &copy; 2010 SGH informationstechnologie UG
 * @version 1.1
 * @access public
 */
abstract class Enum
{
	/*
	 * Configuration Start. Change these before defining Enums.
	 */
	/**
	 * Enable cache (stores generated enum classes in cache dir)
	 * @var bool
	 * @static
	 */
	public static $cacheEnabled = true;
	/**
	 * Check if cached files are outdated (you may want to disable this in productive environment)
	 * @var bool
	 * @static
	 */
	public static $checkCache = true;
	/**
	 * Where the generated files should be stored (path relative to this file)
	 * @var string
	 * @static
	 */
	public static $cacheDir = 'cache';
	/**
	 * If package path should be mapped to cache dir
	 * 
	 * Useful if the same Enum names can occur in different packages. Example:
	 * <code>
	 * // in package1/subpackage/class.php:
	 * Enum::$cacheReUsePackagePath = true;
	 * Enum::define('MyEnum', 'FOO', 'BAR');
	 * 
	 * // in package2/otherclass.php:
	 * Enum::$cacheReUsePackagePath = true;
	 * Enum::define('MyEnum', 'MOO', 'NARF');
	 * </code>
	 * 
	 * Now there will be two separate cached files:
	 * - cache/package1/subpackage/MyEnum.php
	 * - cache/package2/MyEnum.php
	 * 
	 * Of course both packages cannot be used simultaneous since we don't have
	 * namespaces here.
	 * 
	 * @var bool
	 * @static
	 */
	public static $cacheReUsePackagePath = false;
	/**
	 * Package root (necessary for $cacheReUsePackagePath)
	 * @var string
	 * @static
	 */
	public static $cachePackageRoot = '/';
	/*
	 * Configuration End
	 */
	
	/**
	 * @var string Name (value) of the enum instance
	 * @access private
	 */
	private $value;
	
	/**
	 * @var array Two dimensional array collecting all enum instances by class name and name (value)
	 * @access private
	 */
	private static $instances = array();
	
	/**
	 * Constructor for enum instances
	 * 
	 * @param string $value
	 * @return
	 */
	protected final function __construct($value)
	{
	    $this->value = (string)$value;
	}
	
	/**
	 * empty private Clone Constructor: Prevents cloning Enum instances
	 * 
	 * @return void
	 */
	private final function __clone() { }
	/**
	 * String value of enum instance
	 * 
	 * @return
	 */
	public function __toString()
	{
	    return $this->value;
	}

	/*
	 * Private methods start with triple underscore to avoid collision with concrete getters
	 * in generated subclasses
	 */

	/**
	 * Returns enum instance according to calling class and method.
	 * For more performance class and value may also be provided as parameter.
	 * 
	 * @return Enum
	 */
	protected static final function ___get($className=null, $value=null)
	{
		if ($className===null || $value===null) {
	        $d = debug_backtrace(false);
	        $value = $d[1]['function'];
	        $className = $d[1]['class'];
		}
	    if (!isset(self::$instances[$className][$value])) {
	        self::$instances[$className][$value] = new $className($value);
	    }
	    return self::$instances[$className][$value];
	}

	/**
	 * Checks, if a string may be used as class name or method name
	 * 
	 * @param string $name
	 * @return bool
	 */
	private static final function ___validName($name)
	{
		if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
			// invalid name
			return false;
		}
		$test = '<?' . $name;
		$tokens = (token_get_all($test));
		if ($tokens[1][0]!==T_STRING) {
			// reserved keyword
			return false;
		}
		return true;
	}
	/**
	 * Generates code for enum class, including "<?php"
	 * 
	 * @param string $enumName
	 * @param array $enumValues
	 * @return
	 */
	private static final function ___generateCode($enumName, array $enumValues)
	{
		$code  = "<?php\n";
		$code .= sprintf("final class %s extends %s {\n", $enumName, __CLASS__);
		foreach($enumValues as $value) {
			$code .= sprintf(
				"\tpublic static final function %s() { return self::___get('%s', '%s'); }\n",
				$value, $enumName, $value
			);
		}
		$code .= "}";
		return $code;
	}
	/**
	 * Generates code for a cache check file
	 * 
	 * @param string $definingFile Full filename of file where Enum::define() was called
	 * @return
	 */
	private static final function ___generateCheckCode($definingFile)
	{
		$checkCode  = "<?php\n"; 
		$checkCode .= "// check if generator still is at the same location (not true when the whole application has moved)\n";
		$checkCode .= "if (!file_exists('" . addcslashes(__FILE__, "'\\") . "')) {\n";
		$checkCode .= "\treturn false;\n";
		$checkCode .= "}\n";
		$checkCode .= "// check if generator changed since last caching\n";
		$checkCode .= "if (filemtime('" . addcslashes(__FILE__, "'\\") . "') > filemtime(__FILE__)) {\n";
		$checkCode .= "\treturn false;\n";
		$checkCode .= "}\n";
		$checkCode .= "// check if defining file changed since last caching\n";
		$checkCode .= "if (!file_exists('" . addcslashes($definingFile, "'\\") . "')) {\n";
		$checkCode .= "\treturn false;\n";
		$checkCode .= "}\n";
		$checkCode .= "if (filemtime('" . addcslashes($definingFile, "'\\") . "') > filemtime(__FILE__)) {\n";
		$checkCode .= "\treturn false;\n";
		$checkCode .= "}\n";
		$checkCode .= "return true;\n";
		return $checkCode;
	}
    /**
     * Gets cache directory
     * 
     * @param string $definingFile Full filename of file where Enum::define() was called. Only used if Enum::$cacheReUsePackagePath is true.
     * @return string
     */
    private static final function ___getCacheDir($definingFile)
    {
    	if (self::$cacheReUsePackagePath) {
    		$packagePath = realpath(dirname($definingFile));
    		$o = strpos($packagePath, self::$cachePackageRoot);
    		if ($o!==false) {
    			return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$cacheDir.
					DIRECTORY_SEPARATOR . substr($packagePath, strlen(self::$cachePackageRoot));
    		} else {
    			trigger_error(sprintf(
					'Enum defined in <b>%s</b> outside package directory <b>%s</b>. ' .
					'Enum::$cacheReUsePackagePath could not be applied.',
					$definingFile,
					self::$cachePackageRoot
				), E_USER_NOTICE);
    			return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$cacheDir;
    		}
    	} else {
	    	return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$cacheDir;
    	}
    }
	/**
	 * Checks if class file is cached and cached file is not outdated.
	 * 
	 * @param string $enumName
	 * @param string $cacheDir
	 * @return bool
	 */
	private static final function ___inCache($enumName, $cacheDir)
	{
		if (!file_exists($cacheDir . '/' . $enumName . '.php')) return false;
		if (self::$checkCache) {
			if (!file_exists($cacheDir . '/' . $enumName . '.check.inc')) return false;
			return require($cacheDir . '/' . $enumName . '.check.inc');
		} else {
			return true;
		}
	}
	/**
	 * Includes cached class file
	 * 
	 * @param string $enumName
	 * @param string $cacheDir
	 * @return void
	 */
	private static final function ___loadFromCache($enumName, $cacheDir)
	{
		require_once $cacheDir . '/' . $enumName . '.php';
	}
	/**
	 * Generates cache files (class file and check file)
	 * 
	 * @param string $definingFile
	 * @param string $enumName
	 * @param array $enumValues
	 * @param string $cacheDir
	 * @return
	 */
	private static final function ___writeToCache($definingFile, $enumName, $enumValues, $cacheDir)
	{
		if (!is_dir($cacheDir)) {
			mkdir($cacheDir, 0777, true);
		}
		file_put_contents(
			$cacheDir . DIRECTORY_SEPARATOR . $enumName . '.check.inc',
			self::___generateCheckCode($definingFile), LOCK_EX
		);
		return file_put_contents(
			$cacheDir . DIRECTORY_SEPARATOR . $enumName . '.php',
			self::___generateCode($enumName, $enumValues),  LOCK_EX
		);
	}

	/**
	 * Automated creation, except if an Enum with this name already is defined
	 * 
     * @param string $enumName
     * @param string $value1
     * @param string $value2
	 * @param ...
     * @return void
	 */
	public static function define_once($enumName /*[, $value1, $value2, ... ]*/ )
	{
		if (class_exists($enumName) && is_subclass_of($enumName, 'Enum')) {
			return;
		}
		$params = func_get_args();
		call_user_func_array(array('Enum','define'), $params);
	}
	/**
	 * Automated creation
	 * 
	 * Note: reserved names deactivated. Public static methods define, define_once and valuesOf can be overridden without problems, private static methods are protected with three leading underscores
	 * 
	 * @param string $enumName
	 * @param string $value1
	 * @param string $value2
	 * @param ...
	 * @return void
	 */
	public static function define($enumName /*[, $value1, $value2, ... ]*/ )
	{
		// validate parameters:
		if (class_exists($enumName)) {
			throw new InvalidArgumentException(__FUNCTION__ . ": Class $enumName already exists.");
		}
/*
		$rc = new ReflectionClass('Enum');
		$reserved = array();
		foreach ($rc->getMethods() as $method) {
			$reserved[] = strtolower($method->name);
		}
*/
		foreach (func_get_args() as $key => $arg) {
			if (!self::___validName($arg)) {
				throw new InvalidArgumentException(__FUNCTION__ . ': Argument ' . ($key + 1) .
					' has to be a valid class/function name');
			}
			if ($key > 0 && substr($arg, 0, 2)==='__') {
				throw new InvalidArgumentException(__FUNCTION__ . ': Argument ' . ($key + 1) .
					' may not start with double underscore');
			}
/*
			if ($key > 0 && in_array(strtolower($arg), $reserved)) {
				throw new InvalidArgumentException(__FUNCTION__ . ": Argument " . ($key + 1) .
					" is a reserved method name ($arg)");
			}
*/
		}
		$_values = func_get_args();
		array_shift($_values);
		$enumValues = array_iunique($_values);
		if ($_values!==$enumValues) {
			$d = debug_backtrace(false);
			trigger_error(sprintf(
				'Enum definition in <b>%s</b> has duplicate values: %s',
				@$d[1]['file'],
				join(',', array_diff_assoc($_values, $enumValues))
				), E_USER_NOTICE
			);
		}
		// finished validating parameters

		if (self::$cacheEnabled) {
			// get defining file, if not possible (i.e. eval'd code), fallback to this file
			$d = debug_backtrace(false);
			$definingFile = isset($d[1],$d[1]['file']) ? $d[1]['file'] : __FILE__;
			$cacheDir = self::___getCacheDir($definingFile);
			// try loading from cache
			if (self::___inCache($enumName, $cacheDir)) {
				self::___loadFromCache($enumName, $cacheDir);
				return;
			}
			// try caching
			if (self::___writeToCache($definingFile, $enumName, $enumValues, $cacheDir)) {
				self::___loadFromCache($enumName, $cacheDir);
				return;
			}
		}

		// caching failed or disabled; eval:
		eval('?>' . self::___generateCode($enumName, $enumValues));
    }
    /**
     * Enum::valuesOf()
     * 
     * @param string $enumName An Enum class
     * @return array All values of that enum class as strings
     */
    public static function valuesOf($enumName)
    {
        if (!is_subclass_of($enumName, __CLASS__)) {
            throw new InvalidArgumentException($enumName . ' is not defined or no Enumeration.');
        }
        $values = array();
        $rc = new ReflectionClass($enumName);
        foreach ($rc->getMethods() as $method) {
            if ($method->getDeclaringClass()->name === $enumName && $method->isStatic()) {
                $values[] = call_user_func(array($enumName, $method->name));
            }
        }
        return $values;
    }
}

?>