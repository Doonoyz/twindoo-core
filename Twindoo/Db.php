<?php
/**
 * Database handling
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
class Twindoo_Db {

	/**
	 * database instance
	 *
	 * @var resource
	 */
	private $_db;
	/**
	 * Id of the last insert
	 *
	 * @var int
	 */
	private $_id;
	/**
	 * Show debug informations
	 *
	 * @var int 0 => nothing, 1 => show insert only , 2 => show select only, 3 => show both
	 */
	private $_showDebug = 0;

	/**
	 * Parameters for mysql connection
	 *
	 * @var Array
	 */
	private $_params = Array ();

	/**
	 * Parameters for User database connection
	 *
	 * @var Array
	 */
	static private $_settingUser = Array ("host" => "localhost",
									"username" => "twindoo_user",
									"password" => "H{kg-ar]",
									"dbname" => "twindoo_user" );

	/**
	 * Static function to connect DB within config and Zend_Db
	 *
	 * @param string $setting Settings to connect DB, if null, user config.ini informations, can be "user" to connect in user DB or "location" to connect location service
	 *
	 * @return Zend_Db|NULL
	 */
	public static function getDb($setting = NULL) {
		$config = new Zend_Config_Ini ( ROOT_DIR . 'application/config.ini', ENVIRONMENT );
		if ($setting === NULL) {
			return (Zend_Db::factory ( $config->db->adapter, $config->db->config->toArray () ));
		} else {
			try {
				$const = '_setting' . ucfirst ( strtolower ( $setting ) );
				return (Zend_Db::factory ( 'PDO_MYSQL', self::$$const ));
			} catch (Exception $e) {
				return (NULL);
			}
		}
	}

	/**
	 * Constructor forbidden, static only instance
	 *
	 * @return NULL
	 */
	private function __construct() {
		throw new TwindooDbException('Must call Twindoo_Db::getDb() instead');
		return;
	}
}

/**
 * TwindooDbException thrown if class called be constructor
 *
 */
class TwindooDbException extends Zend_Exception {
}

