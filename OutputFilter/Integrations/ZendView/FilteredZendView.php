<?php
/**
 * Usage example:
 * 
 * // I) View initialization
 * $view = new FilteredZendView();                   // instead of $view = new Zend_View()
 * $view->setOutputFilterWrapper(new OutputFilterWrapper(new HtmlEntitiesFilter));
 * 
 * // II) Assigning variables
 * $view->foo = "bar";                               // as usual
 * 
 * // III) Rendering View
 * $view->render();                                  // as usual
 * 
 * 
 * @author fs
 * @link http://zendframework.com/manual/en/zend.view.scripts.html integration in full zf app
 * @todo protect private attributes: FilteredZendViewAbstract extends Zend_View
 * @todo implement as decorator, should work with any Zend_View_Interface implementation
 *
 */
class FilteredZendView extends Zend_View
{
	/**
	 * @var OutputFilterWrapperInterface
	 */
	private $_outputFilterWrapper;

	public function __construct($config = array())
	{
		if (array_key_exists('outputFilterWrapper', $config) && $config['outputFilterWrapper'] instanceof OutputFilterWrapperInterface) {
			$this->_outputFilterWrapper = $config['outputFilterWrapper'];
		} else {
			$this->_outputFilterWrapper = new OutputFilterWrapper();
		}
		parent::__construct($config);
	}

	/**
	 * @param OutputFilterWrapperInterface $w
	 */
	public function setOutputFilterWrapper(OutputFilterWrapperInterface $w) {
		$this->_outputFilterWrapper = $w;
	}

	public function __set($key, $val)
	{
		parent::__set($key, $this->_outputFilterWrapper->filterRecursive($val, OutputFilterWrapperConstraints::ARRAY_KEY, $key));
	}
}