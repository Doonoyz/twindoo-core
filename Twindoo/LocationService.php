<?php
/**
 * This class retrieve all informations for searching a city by its name/country/postcode
 *
 * <code>
 * $result = Twindoo_LocationService::getInfos('reau', 'France');
 * $latitude = $result['LATITUDE'];
 * $longitude = $result['LONGITUDE'];
 *
 * $result = Twindoo_LocationService::getIpInfo('127.0.0.1');
 * $city = $result['CITY'];
 * $country = $result['COUNTRY'];
 * </code>
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
class Twindoo_LocationService implements Twindoo_LocationService_Ip_Interface {
	/**
	 * All the countrie by their country code
	 *
	 */
	static private $_countries = array();
	/**
	 * Retrieve countries
	 *
	 * @return Array Array containing countries by their country code
	 */
	public static function getCountries() {
		self::$_countries = Array('AD' => t_( "Andorra"),
								'AE' => t_( "United Arab Emirates"),
								'AF' => t_( "Afghanistan"),
								'AG' => t_( "Antigua and Barbuda"),
								'AI' => t_( "Anguilla"),
								'AL' => t_( "Albania"),
								'AM' => t_( "Armenia"),
								'AN' => t_( "Netherlands Antilles"),
								'AO' => t_( "Angola"),
								'AQ' => t_( "Antarctica"),
								'AR' => t_( "Argentina"),
								'AS' => t_( "American Samoa"),
								'AT' => t_( "Austria"),
								'AU' => t_( "Australia"),
								'AW' => t_( "Aruba"),
								'AX' => t_( "Aland Islands"),
								'AZ' => t_( "Azerbaijan"),
								'BA' => t_( "Bosnia and Herzegovina"),
								'BB' => t_( "Barbados"),
								'BD' => t_( "Bangladesh"),
								'BE' => t_( "Belgium"),
								'BF' => t_( "Burkina Faso"),
								'BG' => t_( "Bulgaria"),
								'BH' => t_( "Bahrain"),
								'BI' => t_( "Burundi"),
								'BJ' => t_( "Benin"),
								'BL' => t_( "Saint Barthelemy"),
								'BM' => t_( "Bermuda"),
								'BN' => t_( "Brunei"),
								'BO' => t_( "Bolivia"),
								'BR' => t_( "Brazil"),
								'BS' => t_( "Bahamas"),
								'BT' => t_( "Bhutan"),
								'BV' => t_( "Bouvet Island"),
								'BW' => t_( "Botswana"),
								'BY' => t_( "Belarus"),
								'BZ' => t_( "Belize"),
								'CA' => t_( "Canada"),
								'CC' => t_( "Cocos Islands"),
								'CD' => t_( "Democratic Republic of the Congo"),
								'CF' => t_( "Central African Republic"),
								'CG' => t_( "Republic of the Congo"),
								'CH' => t_( "Switzerland"),
								'CI' => t_( "Ivory Coast"),
								'CK' => t_( "Cook Islands"),
								'CL' => t_( "Chile"),
								'CM' => t_( "Cameroon"),
								'CN' => t_( "China"),
								'CO' => t_( "Colombia"),
								'CR' => t_( "Costa Rica"),
								'CU' => t_( "Cuba"),
								'CV' => t_( "Cape Verde"),
								'CX' => t_( "Christmas Island"),
								'CY' => t_( "Cyprus"),
								'CZ' => t_( "Czech Republic"),
								'DE' => t_( "Germany"),
								'DJ' => t_( "Djibouti"),
								'DK' => t_( "Denmark"),
								'DM' => t_( "Dominica"),
								'DO' => t_( "Dominican Republic"),
								'DZ' => t_( "Algeria"),
								'EC' => t_( "Ecuador"),
								'EE' => t_( "Estonia"),
								'EG' => t_( "Egypt"),
								'EH' => t_( "Western Sahara"),
								'ER' => t_( "Eritrea"),
								'ES' => t_( "Spain"),
								'ET' => t_( "Ethiopia"),
								'FI' => t_( "Finland"),
								'FJ' => t_( "Fiji"),
								'FK' => t_( "Falkland Islands"),
								'FM' => t_( "Micronesia"),
								'FO' => t_( "Faroe Islands"),
								'FR' => t_( "France"),
								'GA' => t_( "Gabon"),
								'GB' => t_( "United Kingdom"),
								'GD' => t_( "Grenada"),
								'GE' => t_( "Georgia"),
								'GF' => t_( "French Guiana"),
								'GG' => t_( "Guernsey"),
								'GH' => t_( "Ghana"),
								'GI' => t_( "Gibraltar"),
								'GL' => t_( "Greenland"),
								'GM' => t_( "Gambia"),
								'GN' => t_( "Guinea"),
								'GP' => t_( "Guadeloupe"),
								'GQ' => t_( "Equatorial Guinea"),
								'GR' => t_( "Greece"),
								'GS' => t_( "South Georgia and the South Sandwich Islands"),
								'GT' => t_( "Guatemala"),
								'GU' => t_( "Guam"),
								'GW' => t_( "Guinea-Bissau"),
								'GY' => t_( "Guyana"),
								'HK' => t_( "Hong Kong"),
								'HM' => t_( "Heard Island and McDonald Islands"),
								'HN' => t_( "Honduras"),
								'HR' => t_( "Croatia"),
								'HT' => t_( "Haiti"),
								'HU' => t_( "Hungary"),
								'ID' => t_( "Indonesia"),
								'IE' => t_( "Ireland"),
								'IL' => t_( "Israel"),
								'IM' => t_( "Isle of Man"),
								'IN' => t_( "India"),
								'IO' => t_( "British Indian Ocean Territory"),
								'IQ' => t_( "Iraq"),
								'IR' => t_( "Iran"),
								'IS' => t_( "Iceland"),
								'IT' => t_( "Italy"),
								'JE' => t_( "Jersey"),
								'JM' => t_( "Jamaica"),
								'JO' => t_( "Jordan"),
								'JP' => t_( "Japan"),
								'KE' => t_( "Kenya"),
								'KG' => t_( "Kyrgyzstan"),
								'KH' => t_( "Cambodia"),
								'KI' => t_( "Kiribati"),
								'KM' => t_( "Comoros"),
								'KN' => t_( "Saint Kitts and Nevis"),
								'KP' => t_( "North Korea"),
								'KR' => t_( "South Korea"),
								'KW' => t_( "Kuwait"),
								'KY' => t_( "Cayman Islands"),
								'KZ' => t_( "Kazakhstan"),
								'LA' => t_( "Laos"),
								'LB' => t_( "Lebanon"),
								'LC' => t_( "Saint Lucia"),
								'LI' => t_( "Liechtenstein"),
								'LK' => t_( "Sri Lanka"),
								'LR' => t_( "Liberia"),
								'LS' => t_( "Lesotho"),
								'LT' => t_( "Lithuania"),
								'LU' => t_( "Luxembourg"),
								'LV' => t_( "Latvia"),
								'LY' => t_( "Libya"),
								'MA' => t_( "Morocco"),
								'MC' => t_( "Monaco"),
								'MD' => t_( "Moldova"),
								'ME' => t_( "Montenegro"),
								'MF' => t_( "Saint Martin"),
								'MG' => t_( "Madagascar"),
								'MH' => t_( "Marshall Islands"),
								'MK' => t_( "Macedonia"),
								'ML' => t_( "Mali"),
								'MM' => t_( "Myanmar"),
								'MN' => t_( "Mongolia"),
								'MO' => t_( "Macao"),
								'MP' => t_( "Northern Mariana Islands"),
								'MQ' => t_( "Martinique"),
								'MR' => t_( "Mauritania"),
								'MS' => t_( "Montserrat"),
								'MT' => t_( "Malta"),
								'MU' => t_( "Mauritius"),
								'MV' => t_( "Maldives"),
								'MW' => t_( "Malawi"),
								'MX' => t_( "Mexico"),
								'MY' => t_( "Malaysia"),
								'MZ' => t_( "Mozambique"),
								'NA' => t_( "Namibia"),
								'NC' => t_( "New Caledonia"),
								'NE' => t_( "Niger"),
								'NF' => t_( "Norfolk Island"),
								'NG' => t_( "Nigeria"),
								'NI' => t_( "Nicaragua"),
								'NL' => t_( "Netherlands"),
								'NO' => t_( "Norway"),
								'NP' => t_( "Nepal"),
								'NR' => t_( "Nauru"),
								'NU' => t_( "Niue"),
								'NZ' => t_( "New Zealand"),
								'OM' => t_( "Oman"),
								'PA' => t_( "Panama"),
								'PE' => t_( "Peru"),
								'PF' => t_( "French Polynesia"),
								'PG' => t_( "Papua New Guinea"),
								'PH' => t_( "Philippines"),
								'PK' => t_( "Pakistan"),
								'PL' => t_( "Poland"),
								'PM' => t_( "Saint Pierre and Miquelon"),
								'PN' => t_( "Pitcairn"),
								'PR' => t_( "Puerto Rico"),
								'PS' => t_( "Palestinian Territory"),
								'PT' => t_( "Portugal"),
								'PW' => t_( "Palau"),
								'PY' => t_( "Paraguay"),
								'QA' => t_( "Qatar"),
								'RE' => t_( "Reunion"),
								'RO' => t_( "Romania"),
								'RS' => t_( "Serbia"),
								'RU' => t_( "Russia"),
								'RW' => t_( "Rwanda"),
								'SA' => t_( "Saudi Arabia"),
								'SB' => t_( "Solomon Islands"),
								'SC' => t_( "Seychelles"),
								'SD' => t_( "Sudan"),
								'SE' => t_( "Sweden"),
								'SG' => t_( "Singapore"),
								'SH' => t_( "Saint Helena"),
								'SI' => t_( "Slovenia"),
								'SJ' => t_( "Svalbard and Jan Mayen"),
								'SK' => t_( "Slovakia"),
								'SL' => t_( "Sierra Leone"),
								'SM' => t_( "San Marino"),
								'SN' => t_( "Senegal"),
								'SO' => t_( "Somalia"),
								'SR' => t_( "Suriname"),
								'ST' => t_( "Sao Tome and Principe"),
								'SV' => t_( "El Salvador"),
								'SY' => t_( "Syria"),
								'SZ' => t_( "Swaziland"),
								'TC' => t_( "Turks and Caicos Islands"),
								'TD' => t_( "Chad"),
								'TF' => t_( "French Southern Territories"),
								'TG' => t_( "Togo"),
								'TH' => t_( "Thailand"),
								'TJ' => t_( "Tajikistan"),
								'TK' => t_( "Tokelau"),
								'TL' => t_( "East Timor"),
								'TM' => t_( "Turkmenistan"),
								'TN' => t_( "Tunisia"),
								'TO' => t_( "Tonga"),
								'TR' => t_( "Turkey"),
								'TT' => t_( "Trinidad and Tobago"),
								'TV' => t_( "Tuvalu"),
								'TW' => t_( "Taiwan"),
								'TZ' => t_( "Tanzania"),
								'UA' => t_( "Ukraine"),
								'UG' => t_( "Uganda"),
								'UM' => t_( "United States Minor Outlying Islands"),
								'US' => t_( "United States"),
								'UY' => t_( "Uruguay"),
								'UZ' => t_( "Uzbekistan"),
								'VA' => t_( "Vatican"),
								'VC' => t_( "Saint Vincent and the Grenadines"),
								'VE' => t_( "Venezuela"),
								'VG' => t_( "British Virgin Islands"),
								'VI' => t_( "U.S. Virgin Islands"),
								'VN' => t_( "Vietnam"),
								'VU' => t_( "Vanuatu"),
								'WF' => t_( "Wallis and Futuna"),
								'WS' => t_( "Samoa"),
								'YE' => t_( "Yemen"),
								'YT' => t_( "Mayotte"),
								'ZA' => t_( "South Africa"),
								'ZM' => t_( "Zambia"),
								'ZW' => t_( "Zimbabwe"),
								'CS' => t_( "Serbia and Montenegro"));
		return (self::$_countries);
	}
	
	/**
	 * Compute distance between two cities in KM
	 *
	 * @param float	$lat1 Latitude starting point
	 * @param float	$lon1 Longitude starting point
	 * @param float	$lat2 Latitude arriving point
	 * @param float	$lon2 Longitude arriving point
	 *
	 * @return float distance between these points in Km
	 */
	static public function distance($lat1, $lon1, $lat2, $lon2) {
		$lat1 = deg2rad ( $lat1 );
		$lat2 = deg2rad ( $lat2 );
		$lon1 = deg2rad ( $lon1 );
		$lon2 = deg2rad ( $lon2 );

		$R = 6371;
		$dLat = $lat2 - $lat1;
		$dLong = $lon2 - $lon1;
		$var1 = $dLong / 2;
		$var2 = $dLat / 2;
		$a = pow ( sin ( $dLat / 2 ), 2 ) + cos ( $lat1 ) * cos ( $lat2 ) * pow ( sin ( $dLong / 2 ), 2 );
		$c = 2 * atan2 ( sqrt ( $a ), sqrt ( 1 - $a ) );
		$d = $R * $c;
		return $d;
	}
	
	/**
	 * Return informations for a city
	 *
	 * @param string $city    City to search
	 * @param string $country Country to search
	 * @param int    $postal  Zip code to search
	 *
	 * @return Array Array containing informations
	 */
	static public function getInfos($city, $country, $postal = NULL) {
		$servicesOrder = array('Twindoo_LocationService_Geonames_Xml');
		foreach ($servicesOrder as $servicesOrder) {
			try {
				$result = call_user_func_array(array($servicesOrder, 'getInfos'), array($city, $country, $postal));
				return ($result);
			} catch (Twindoo_LocationService_Exception $e) {
			}
		}
		throw new Twindoo_LocationService_Exception('not found');
	}
	
	/**
	 * Return informations for an IP
	 *
	 * @param string $ip IP for geolocation
	 *
	 * @return Array Array containing informations
	 */
	static public function getIpInfos($ip = null) {
		if ($ip == null) {
			$ip = Twindoo_Utile::getIp();
		}
		$servicesOrder = array('Twindoo_LocationService_Ip_Blogama_Xml');
		foreach ($servicesOrder as $servicesOrder) {
			try {
				$result = call_user_func_array(array($servicesOrder, 'getIpInfos'), array($ip));
				if (isset($result['CITY']) && isset($result['COUNTRY'])) {
					return ($result);
				}
			} catch (Exception $e) {
			}
		}
		throw new Twindoo_LocationService_Exception('not found');
	}
}

class Twindoo_LocationService_Exception extends Zend_Exception {
}
