<?php
/**
 * Ajax controller 
 *
 * @package    Core
 * @subpackage controller
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
class Twindoo_Controller_AjaxController extends Zend_Controller_Action {
	
	/**
	 * Protect from direct access
	 *
	 */
	public function indexAction() {
		$this->_redirect ( '/' );
	}
	
	/**
	 * Use the right Ajax Component
	 */
	public function init() {
		$this->getHelper ( 'viewRenderer' )->setNoRender ();
		$name = strtolower($this->_request->getActionName());
		if (method_exists($this, Zend_Controller_Front::getInstance()->getDispatcher()->formatActionName($name))) {
            return;
		}
		$controllerDir = Zend_Controller_Front::getInstance()->getControllerDirectory();
		reset($controllerDir);
		$controller = current($controllerDir);
		$file = realpath($controller . '/../ajax/' . ucfirst($name) . '.php');
		$this->view->setView($name);
		$className = 'Ajax_' . ucfirst($name);
		@include_once($file);
		if (class_exists($className, false)) {
			$ajax = new $className();
			if (!($ajax instanceof Twindoo_Ajax)) {
                throw new Zend_Controller_Action_Exception();
			}
			$ajax->setController($this);
			$ajax->run();
			$ajax->sendAnswer();
			$this->_request->setActionName('fake');
		} else {
			throw new Zend_Controller_Action_Exception();
		}
	}
	
	/**
	 * Fake action to do nothing, used to avoid 404 action error due to lack of action
	 */
	public function fakeAction() {
		$this->getHelper ( 'viewRenderer' )->setNoRender ();
	}
}