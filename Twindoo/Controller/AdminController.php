<?php
/**
 * Admin controller 
 *
 * @package    Core
 * @subpackage controller
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
class Twindoo_Controller_AdminController extends Zend_Controller_Action {
	
	/**
	 * Use the right Admin Component
	 */
	public function init() {
		$name = strtolower($this->_request->getActionName());
		if (method_exists($this, Zend_Controller_Front::getInstance()->getDispatcher()->formatActionName($name))) {
            return;
		}
		$controllerDir = Zend_Controller_Front::getInstance()->getControllerDirectory();
		reset($controllerDir);
		$controller = current($controllerDir);
		$file = realpath($controller . '/../admin/' . ucfirst($name) . '.php');
		$this->view->setView($name);
		$className = 'Admin_' . ucfirst($name);
		@include_once($file);
		if (class_exists($className, false)) {
			$admin = new $className();
			if (!($admin instanceof Twindoo_Admin)) {
                throw new Zend_Controller_Action_Exception();
			}
			$admin->setController($this);
            $admin->run();
            if (!$this->getHelper('viewRenderer')->getNoRender()) {
				$this->view->display();
			} else {
				$admin->sendAnswer();
			}
			$this->_request->setActionName('fake');
		} else {
			throw new Zend_Controller_Action_Exception();
		}
	}
	
	/**
	 * Fake action to do nothing, used to avoid 404 action error due to lack of action
	 */
	public function fakeAction() {
		$this->getHelper('viewRenderer')->setNoRender();
	}
}