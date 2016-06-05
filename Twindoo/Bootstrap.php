<?php
/**
 * General bootstrap
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
class Twindoo_Bootstrap {
	public function __construct($projectName) {
	}

	public function start() {
		$this->startSession();
	}
	
	public function startSession() {
		Zend_Session::start();
		Zend_Session::regenerateId();
		Twindoo_User::getConnectedByCookie();
	}
	
	public function startRegistry() {
		$this->_registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
		Zend_Registry::setInstance($this->_registry);
	}
}