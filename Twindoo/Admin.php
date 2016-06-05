<?php
/**
 * Admin template class
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
abstract class Twindoo_Admin {

	/**
	 * Object to the Current Controller
	 *
	 * @var	Array
	 */
	protected $_controller;
	
	/**
	 * Answer to be JSONified
	 *
	 * @var	Array
	 */
	protected $_answer = null;
	
	/**
	 * View engine
	 *
	 * @var	Zend_View
	 */
	protected $view;
	
	/**
	 * Template file path
	 *
	 * @var string
	 */
	public $_file;

	/**
	 * Define Controller
	 *
	 */
	public function setController(Zend_Controller_Action $controller) {
		$this->_controller = $controller;
		$this->view = $this->_controller->view;
	}
	
	/**
	 * Define the view in the view renderer
	 *
	 * @param object $engine   Template Engine
	 * @param string $filename View file path
	 */
	public function setView($filename) {
		$this->_file = $filename;
	}
	/**
	 * Run method to be implemented by Admin classes
	 */
	abstract public function run();
	
	/**
	 * Send the ajax answer
	 */
	public function sendAnswer($error = false) {
		if ($this->_controller->getHelper ( 'viewRenderer' )->getNoRender ()) {
			if (Zend_Controller_Action_HelperBroker::hasHelper('csrf')) {
				$this->_addAnswer('csrf', Zend_Controller_Action_HelperBroker::getExistingHelper('csrf')->getToken());
			}
			$response = $this->_controller->getResponse();
			$response->clearBody();
			$response->setHeader('Content-Type', 'text/html; charset=UTF-8', true);
			if (count($this->_answer) == 1) {
				$response->appendBody($this->_answer['default']);
			} else {
				$response->appendBody(Zend_Json::encode($this->_answer));
			}
		} else {
			$this->view->forceRender();
		}
	}

	/**
	 * Adds an answer
	 *
	 * @param string $key   Answer name
	 * @param mixed	 $value Answer value
	 */
	protected function _addAnswer($key, $value) {
		$this->_answer[$key] = $value;
	}

	/**
	 * Set the answer
	 *
	 * @param mixed $value Answer value
	 */
	protected function _setAnswer($value) {
		$this->_answer = array();
		$this->_answer ['default'] = $value;
	}
}