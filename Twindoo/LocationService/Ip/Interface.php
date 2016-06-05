<?php
/**
 * Default interface for an IP location service
 *
 * @package    Core
 * @subpackage LocationService/Ip
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
interface Twindoo_LocationService_Ip_Interface {
	/**
	 * Get informations
	 *
	 * @param string $ip Ip to geolocalise
	 */
	static public function getIpInfos($ip = null);
}