<?php
require_once(dirname(__FILE__).'/../../Smarty/Smarty.class.php');
/**
 * Permit to never cache the content specified in the dynamic keyword
 *
 * @param unknown_type $param   unknown description
 * @param string	   $content Content to be parse 
 * @param Smarty	   $smarty  Smarty engine
 * 
 * @return string Treated string
 */
function smarty_block_dynamic($param, $content, &$smarty) {
	return $content;
}

/**
 * Template renderer, abstract to avoid its usage, must be inherited
 *
 * @package    Core
 * @subpackage view
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
abstract class Twindoo_View_Smarty extends Zend_View_Abstract {
	/**
	 * Smarty object
	 * 
	 * @var Smarty
	 */
	protected $_smarty;

	/**
	 * Layout
	 *
	 * @var string
	 */
	protected $_layout = NULL;

	/**
	 * Layout values
	 *
	 * @var Array
	 */
	protected $_layoutValues = array ();

	/**
	 * Texts to be added in JS
	 *
	 * @var Array
	 */
	protected $_JSTexts = array ();

	/**
	 * Current template
	 *
	 * @var string
	 */
	protected $_fileTemplate = null;

	/**
	 * Cache key
	 *
	 * @var string
	 */
	protected $_cache_key = null;
	
	/**
	 * Constructor
	 *
	 * Pass it a an array with the following configuration options:
	 *
	 * scriptPath: the directory where your templates reside
	 * compileDir: the directory where you want your compiled templates (must be
	 * writable by the webserver)
	 * configDir: the directory where your configuration files reside
	 *
	 * both scriptPath and compileDir are mandatory options, as Smarty needs
	 * them. You can't set a cacheDir, if you want caching use Zend_Cache
	 * instead, adding caching to the view explicitly would alter behaviour
	 * from Zend_View.
	 *
	 * @see Zend_View::__construct
	 * @param array $config Array of smarty parameters
	 * 
	 * @throws Exception
	 */
	public function __construct($config = array()) {
		$this->_smarty = new Smarty ( );

		$this->_smarty->register_block ( 'dynamic', 'smarty_block_dynamic', false );
		if (! array_key_exists ( 'scriptPath', $config )) {
			throw new Exception ( 'scriptPath must be set in $config for ' . get_class ( $this ) );
		}
		//check for scriptPath
		//@see Zend_View::__construct
		if (! array_key_exists ( 'compileDir', $config )) {
			throw new Exception ( 'compileDir must be set in $config for ' . get_class ( $this ) );
		} else {
			$this->_smarty->compile_dir = $config ['compileDir'];
		}

		//compile dir
		if (array_key_exists ( 'compileCheck', $config )) {
			$this->_smarty->compile_check = $config ['compileCheck'];
		}
		if (array_key_exists ( 'configDir', $config )) {
			$this->_smarty->config_dir = $config ['configDir'];
		}
		//cache system
		if (array_key_exists ( 'cache', $config )) {
			$this->_smarty->caching = $config ['cache'];
		}
		if (array_key_exists ( 'cacheLife', $config )) {
			$this->_smarty->cache_lifetime = $config ['cacheLife'];
		}
		if (array_key_exists ( 'cacheDir', $config )) {
			$this->_smarty->cache_dir = $config ['cacheDir'];
		}

		parent::__construct ( $config );
		//call parent constructor
	}
	/**
	 * Return the template engine object
	 *
	 * @return Smarty
	 */
	public function getEngine() {
		return $this->_smarty;
	}
	/**
	 * Set the path to the templates
	 *
	 * Smarty can only handle one path
	 *
	 * @see Zend_View_Abstract::__construct
	 * @param string $path template directory
	 * 
	 */
	public function setScriptPath($path) {
		if (is_readable ( $path )) {
			$this->_smarty->template_dir = $path;
			parent::addScriptPath ( $path );
			return;
		}
		//valid path, set it as the template_dir
		if ($path) {
			//we did try to load a real path but it failed
			throw new Exception ( 'Invalid path provided: ' . $path );
		} else {
			//no real path provided, reset it
			$this->_smarty->template_dir = '';
		}
	}
	
	/**
	 * Set the layout
	 *
	 * @param string $layout Layout name
	 */
	public function setLayout($layout) {
		if (! preg_match ( '/\.tpl$/', $layout )) {
			$layout = $layout . '.tpl';
		}
		$this->_layout = $layout;
	}
	/**
	 * Set the view to display
	 *
	 * @param string $file name of the view
	 */
	public function setView($file) {
		$this->_fileTemplate = strtolower ( $file );
	}
	/**
	 * Add a script path
	 *
	 * smarty can only handle one path, so this calls setScriptPath()
	 *
	 * @param string $path Path to the scripts
	 */
	public function addScriptPath($path) {
		$this->setScriptPath ( $path );
	}
	/**
	 * Add a layout var
	 *
	 * @param string $name  Variable name
	 * @param string $value Variable value
	 */
	public function addLayoutVar($name, $value) {
		$this->_layoutValues [$name] = $value;
	}
	/**
	 * Adds a Javascript variable text
	 *
	 * @param string $msgId Variable name
	 * @param string $text  Text
	 */
	public function addJSText($msgId, $text) {
		$this->_JSTexts [$msgId] = $text;
	}

	/**
	 * check if a template is cached,
	 *
	 * @param string $key Key for the cache
	 * 
	 * @return bool
	 */
	public function isCached($key = null) {
		$this->prepareFile ();
		return (($key === null) ? $this->_smarty->is_cached ( $this->_fileTemplate ) : $this->_smarty->is_cached ( $this->_fileTemplate, $key ));
	}

	/**
	 * clear the cache for a template,
	 *
	 * @param string $key key for the cache
	 */
	public function clearCache($key = null) {
		$this->prepareFile ();
		if ($key === null)
			$this->_smarty->clear_cache ( $this->_fileTemplate );
		else
			$this->_smarty->clear_cache ( $this->_fileTemplate, $key );
	}

	/**
	 * clear the cache for a key,
	 *
	 * @param string $key key for the cache
	 */
	public function clearAllCache($key = null) {
		if ($key === null)
			$this->_smarty->clear_cache ( null );
		else
			$this->_smarty->clear_cache ( null, $key );
	}

	/**
	 * set a time for the cache life,
	 *
	 * @param int $time time in seconds
	 */
	public function setCacheLife($time = 0) {
		if (is_numeric ( $time ) & $time >= 0)
			$this->_smarty->cache_lifetime = $time;
	}

	/**
	 * set a time for the cache life,
	 *
	 * @param string $key key for the cache
	 */
	public function setCacheKey($key) {
		$this->_cache_key = $key;
	}

	/**
	 * fetch a template, echos the result,
	 *
	 * @see Zend_View_Abstract::render()
	 * @param string $name The template
	 */

	protected function _run() {
		if (!$this->_layout) {
			return;
		}
		$this->prepareFile ();
		$this->strictVars ( true );
		$vars = get_object_vars ( $this );
		foreach ( $vars as $key => $value ) {
			if ('_' != substr ( $key, 0, 1 )) {
				$this->_smarty->assign ( $key, $value );
			}
		}
		//assign variables to the template engine
		$this->_smarty->assign_by_ref ( 'this', $this );
		//why 'this'?
		//to emulate standard zend view functionality
		//doesn't mess up smarty in any way
		$path = $this->getScriptPaths ();
		//smarty needs a template_dir, and can only use templates,
		//found in that directory, so we have to strip it from the filename
		$this->_smarty->template_dir = $path [0];
		//set the template diretory as the first directory from the path
		//process the template (and filter the output)
		$template = ($this->_cache_key === null) ? $this->_smarty->fetch ( $this->_fileTemplate ) : $this->_smarty->fetch ( $this->_fileTemplate, $this->_cache_key );
		// process the layout
		$this->_smarty->clear_all_assign ();
		$this->_smarty->template_dir = substr ( $this->getScriptPath ( $this->_layout ), 0, - (strlen ( $this->_layout )) );
		$this->_initLayout ();

		$this->_layoutValues ['JavaScriptTexts'] = addslashes( Zend_Json::encode ( $this->_JSTexts ) );
		foreach ( $this->_layoutValues as $name => $value ) {
			$this->_smarty->assign ( $name, $value );
		}
		$this->_smarty->assign ( 'templateRenderer', $template );
		$this->_smarty->display ( $this->_layout );
	}

	/**
	 * Function to initialize the Layout var 
	 *
	 */
	abstract protected function _initLayout();

	/**
	 * Prepare the template name by its controller/action name
	 *
	 */
	public function preparefile() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if ($this->_fileTemplate == NULL)
			$this->setView ( $request->getControllerName () . '/' . $request->getActionName () . '.tpl' );
		else if (! preg_match ( '/\.tpl$/', $this->_fileTemplate ))
			$this->setView ( $request->getControllerName () . '/' . $this->_fileTemplate . '.tpl' );

	}
	
	/**
	 * Processes a template and display it
	 */
	public function forceRender() {
		$this->prepareFile ();
		$this->strictVars ( true );
		$vars = get_object_vars ( $this );
		foreach ( $vars as $key => $value ) {
			if ('_' != substr ( $key, 0, 1 )) {
				$this->_smarty->assign ( $key, $value );
			}
		}
		if ($this->_cache_key === null) {
			$this->_smarty->display ( $this->_fileTemplate );
		} else {
			$this->_smarty->display ( $this->_fileTemplate, $this->_cache_key );
		}
	}
	
	/**
	 * Processes a template and display it
	 */
	public function display() {
		$this->_run ();
	}
}