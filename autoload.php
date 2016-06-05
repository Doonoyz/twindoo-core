<?php
require_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Twindoo_');
$loader->setFallbackAutoloader(true);

/**
 * Exception thrown in case of non - existing class
 *
 */
class Undefined_Class_Exception extends Zend_Exception {
}