<?php
/**
 * Blogama IP geolocation in XML format
 *
 * @package    Core
 * @subpackage LocationService/Ip
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
class Twindoo_LocationService_Ip_Blogama_Xml implements Twindoo_LocationService_Ip_Interface {
	/**
	 * Returns informations for an IP
	 *
	 * @param string $ip IP to check
	 *
	 * @return array Array containing informations
	 */
	static public function getIpInfos($ip = null) {
		if ($ip == null) {
			$ip = Twindoo_Utile::getIp();
		}
		$client = new Zend_Rest_Client('http://blogama.org/ip_query.php');
		$result = $client->output('xml')->ip($ip)->get();
		$return = array();
		$return ['IP'] = $result->Ip();
		$return ['STATUS'] = $result->Status();
		$return ['COUNTRYCODE'] = $result->CountryCode();
		$return ['COUNTRY'] = $result->CountryName();
		$return ['REGIONCODE'] = $result->RegionCode();
		$return ['REGIONNAME'] = $result->RegionName();
		$return ['CITY'] = $result->City();
		$return ['ZIPCODE'] = $result->ZipPostalCode();
		$return ['LATITUDE'] = $result->Latitude();
		$return ['LONGITUDE'] = $result->Longitude();
		return ($return);
	}
}