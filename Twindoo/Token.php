<?php
/**
 * Token class to avoid CSRF failure
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
class Twindoo_Token {
	
	protected function __construct() {
		throw new Exception ('Unable to create token, use Twindoo_Toker::createToken() instead');
	}
	
	/**
	 * Create a token
	 *
	 * @param string $key Optionnal key to have different token
	 *
	 * @return string Token generated
	 */
	static public function createToken($key = 'default') {
		$session = new Zend_Session_Namespace(__CLASS__);
		$array = $session->token;
		$array [$key] = uniqid();
		$session->token = $array;
		return ($array [$key]);
	}
	
	/**
	 * Retrieve saved token
	 *
	 * @param string $key Optionnal key to retrieve different token
	 *
	 * @return string Saved token
	 */
	static public function getToken($key = 'default') {
		$session = new Zend_Session_Namespace(__CLASS__);
		$array = $session->token;
		return ( isset ( $array [$key] ) ? $array [$key] : '' );
	}
}