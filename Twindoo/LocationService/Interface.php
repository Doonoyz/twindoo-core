<?php
/**
 * Default interface for an IP location service
 *
 * @package    Core
 * @subpackage LocationService
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
interface Twindoo_LocationService_Interface {
	/**
	 * Return LONG/LAT points for a city in a country
	 *
	 * @param string $city	City name
	 * @param string $country Country name
	 *
	 * @return Array
	 */
	static public function getInfos($city, $country, $postal = NULL);
}