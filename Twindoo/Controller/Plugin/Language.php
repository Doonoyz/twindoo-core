<?php
/**
 * Language Plugin to translate every page in the selected language
 *
 * @package    Core
 * @subpackage controller/plugin
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
class Twindoo_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract {
	
	/**
	 * Check if language param is used and define the locale
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 */
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$value = $request->getParam ( 'language' );
		if ($value) {
		
			$tr = Zend_Registry::getInstance()->translate;
			
			try {
				$locale = new Zend_Locale ( $value );
			} catch ( Zend_Locale_Exception $e ) {
				$locale = new Zend_Locale ( 'en' );
			}
			try {
				$tr->setLocale ( $locale->getLanguage () );
			} catch ( Exception $e ) {
				try {
					$locale = new Zend_Locale ( Twindoo_User::getLocale());
					$tr->setLocale ( $locale->getLanguage () );
				} catch (Exception $e) {
					$locale = new Zend_Locale ( 'en' );
					$tr->setLocale ( $locale->getLanguage () );
				}
			}

			Zend_Registry::getInstance()->translate = $tr;
		}
	}
}