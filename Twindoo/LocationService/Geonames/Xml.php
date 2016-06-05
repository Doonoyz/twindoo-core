<?php
/**
 * This class retrieve all informations for searching a city by its name/country/postcode
 *
 * <code>
 *		$lws = new Twindoo_LocationService();
 *		$lws->setLimit(10)
 *		->setCity("reau")
 *		->setCountry("France")
 *		->setLocale("en")
 *		->run();
 *		if ($lws->getNbResults()) {
 *			$results = $lws->getResults();
 *		}
 * </code>
 *
 * <code>
 * $result = Twindoo_LocationService::getInfos('reau', 'France');
 * $latitude = $result['LATITUDE'];
 * $longitude = $result['LONGITUDE'];
 * </code>
 *
 * @package    Core
 * @subpackage LocationService/Geonames
 * @author     Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright  2008-2009 Doonoyz
 * @version    Paper
 */
class Twindoo_LocationService_Geonames_Xml implements Twindoo_LocationService_Interface {
	/**
	 * Number of maximum results
	 *
	 * @var int
	 */
	private $_limit = 10;
	/**
	 * City to search
	 *
	 * @var string
	 */
	private $_city = null;
	/**
	 * Country to search
	 *
	 * @var string
	 */
	private $_country = null;
	/**
	 * Zip code to search
	 *
	 * @var int
	 */
	private $_postal = null;
	/**
	 * Id of starting result (0 is the first result, 10 is the 11th result)
	 *
	 * @var int
	 */
	private $_start = 0;
	/**
	 * Fcode to search
	 *
	 * @var "PPLC"|"CSTL"|"LK"|"ISL"|"MT"|"PRK"|"AIRP"|"CMTY"|"RSTN"|"HSEC"|"HTL"
	 */
	private $_fcode = "PPLC"; //return cities
	/**
	 * Language result will be returned in
	 *
	 * @var string
	 */
	private $_lang = "en";
	/**
	 * Number of results
	 *
	 * @var int
	 */
	private $_nbResults = null;

	/**
	 * Results Array
	 *
	 * @var array
	 */
	private $_results = Array ();

	/**
	 * Determine how many result will be returned
	 *
	 * @param int $value Number of result returned
	 *
	 * @return self
	 */
	public function setLimit($value = null) {
		$this->_limit = (is_int ( $value ) && $value >= 1) ? $value : null;
		return ($this);
	}

	/**
	 * Determine Fcode to search
	 *
	 * @param FCODE $value Fcode to search
	 *
	 * @return self
	 */
	public function setFCode($value) {
		$codes = Array ("PPLC", //city
"CSTL", //touristique center
"LK", //lake
"ISL", //isle
"MT", //mount
"PRK", //park
"AIRP", //airport
"CMTY", //cemetery
"RSTN", //train station
"HSEC", //castle
"HTL" ); //hostel
		$this->_fcode = $value;
		return ($this);
	}

	/**
	 * Determine the city to search
	 *
	 * @param string $value City to search
	 *
	 * @return self
	 */
	public function setCity($value) {
		$this->_city = rawurlencode ( utf8_encode ( $value ) );
		return ($this);
	}

	/**
	 * Determine country to search
	 *
	 * @param string $value country to search
	 *
	 * @return self
	 */
	public function setCountry($value) {
		$this->_country = rawurlencode ( utf8_encode ( $value ) );
		return ($this);
	}

	/**
	 * Determine zip code to search
	 *
	 * @param int $value Zip code to search
	 *
	 * @return self
	 */
	public function setPostal($value) {
		$this->_postal = $value;
		return ($this);
	}

	/**
	 * Determine the starting value of the results
	 *
	 * @param int $value Starting value of the results
	 *
	 * @return self
	 */
	public function setStart($value = 0) {
		$this->_start = (is_int ( $value ) && $value >= 0) ? $value : 0;
		return ($this);
	}

	/**
	 * Determine result locale
	 *
	 * @param string $value result locale
	 *
	 * @return self
	 */
	public function setLocale($value) {
		$this->_lang = $value;
		return ($this);
	}

	/**
	 * Retrieve result locale
	 *
	 * @return string
	 */
	public function getLocale() {
		return ($this->_lang);
	}

	/**
	 * Retrieve Number max of result to return
	 *
	 * @return int
	 */
	public function getLimit() {
		return ($this->_limit);
	}

	/**
	 * Retrieve Fcode to search
	 *
	 * @return string
	 */
	public function getFCode() {
		return ($this->_fcode);
	}

	/**
	 * Return city to search
	 *
	 * @return string
	 */
	public function getCity() {
		return (utf8_decode ( rawurldecode ( $this->_city ) ));
	}

	/**
	 * Return country to search
	 *
	 * @return string
	 */
	public function getCountry() {
		return (utf8_decode ( rawurldecode ( $this->_country ) ));
	}

	/**
	 * return Zip code to search
	 *
	 * @return int
	 */
	public function getPostal() {
		return ($this->_postal);
	}

	/**
	 * return Starting value for the results
	 *
	 * @return int
	 */
	public function getStart() {
		return ($this->_start);
	}

	/**
	 * Prepare web service if searching with a zip code
	 *
	 * @return string Url of the web service
	 */
	private function _prepareWebServicePostal() {
		$link = "http://ws.geonames.org/postalCodeSearch?postalcode=" . $this->_postal;

		if (! is_null ( $this->_city )) {
			$link .= "&placename=" . $this->_city;
			if (! is_null ( $this->_country ))
				$link .= ",%20" . $this->_country;
		}

		if (! is_null ( $this->_limit ))
			$link .= "&maxRows=" . $this->_limit;

		$link .= "&startRow=" . $this->_start;
		$link .= "&lang=" . $this->_lang;
		return ($link);
	}

	/**
	 * Prepare web service if searching without a zip code
	 *
	 * @return string Url of the web service
	 */
	private function _prepareWebServiceSearch() {
		$link = "http://ws.geonames.org/search?";

		if (! is_null ( $this->_city )) {
			$link .= "q=" . $this->_city;
			if (! is_null ( $this->_country ))
				$link .= ",%20" . $this->_country;
		}

		if (! is_null ( $this->_limit ))
			$link .= "&maxRows=" . $this->_limit;

		$link .= "&startRow=" . $this->_start;
		$link .= "&fcode=" . $this->_fcode;
		$link .= "&lang=" . $this->_lang;
		return ($link);
	}

	/**
	 * Launch the search engine
	 *
	 */
	public function run() {
		if (! is_null ( $this->_postal ))
			$this->_runPostal ();
		else
			$this->_runSearch ();
	}

	/**
	 * Processing the results for a zip code search
	 *
	 * @throws Twindoo_UnableLoadWebService_Exception
	 */
	private function _runPostal() {
		$dom = new DomDocument ( );
		@$dom->load ( $this->_prepareWebServicePostal () );
		if ($dom) {
			$total = $dom->getElementsByTagName ( 'totalResultsCount' );
			foreach ( $total as $totalElement ) {
				$this->_nbResults = $totalElement->nodeValue;
			}
			$code = $dom->getElementsByTagName ( 'code' );
			foreach ( $code as $codeElement ) {
				$array = array ();
				$array ['postalcode'] = $codeElement->childNodes->item ( 1 )->nodeValue;
				$array ['city'] = $codeElement->childNodes->item ( 3 )->nodeValue;
				$array ['countryCode'] = $codeElement->childNodes->item ( 5 )->nodeValue;
				$array ['countryName'] = $this->_country;
				$array ['latitude'] = $codeElement->childNodes->item ( 7 )->nodeValue;
				$array ['longitude'] = $codeElement->childNodes->item ( 9 )->nodeValue;
				$array ['adminCode1'] = $codeElement->childNodes->item ( 11 )->nodeValue;
				$array ['adminName1'] = $codeElement->childNodes->item ( 13 )->nodeValue;
				$array ['adminCode2'] = $codeElement->childNodes->item ( 15 )->nodeValue;
				$array ['adminName2'] = $codeElement->childNodes->item ( 17 )->nodeValue;
				array_push ( $this->_results, $array );
			}
		} else {
			throw new Twindoo_LocationService_Geonames_Xml_Exception ( 'Unable to load webservice !' );
		}
	}

	/**
	 * Processing the results without zip code
	 *
	 * @throws Twindoo_UnableLoadWebService_Exception
	 */
	private function _runSearch() {
		$dom = new DomDocument ( );
		@$dom->load ( $this->_prepareWebServiceSearch () );
		if ($dom) {
			$total = $dom->getElementsByTagName ( 'totalResultsCount' );
			foreach ( $total as $totalElement ) {
				$this->_nbResults = $totalElement->nodeValue;
			}
			$code = $dom->getElementsByTagName ( 'geoname' );
			foreach ( $code as $codeElement ) {
				$array = array ();
				$array ['city'] = $codeElement->childNodes->item ( 1 )->nodeValue;
				$array ['latitude'] = $codeElement->childNodes->item ( 3 )->nodeValue;
				$array ['longitude'] = $codeElement->childNodes->item ( 5 )->nodeValue;
				$array ['geonameId'] = $codeElement->childNodes->item ( 7 )->nodeValue;
				$array ['countryCode'] = $codeElement->childNodes->item ( 9 )->nodeValue;
				$array ['countryName'] = $codeElement->childNodes->item ( 11 )->nodeValue;
				array_push ( $this->_results, $array );
			}
		} else {
			throw new Twindoo_LocationService_Geonames_Xml_Exception ( 'Unable to load webservice !' );
		}
	}

	/**
	 * Return found results
	 *
	 * @return Array
	 */
	public function getResults() {
		return ($this->_results);
	}

	/**
	 * Return number of result found
	 *
	 * @return int
	 */
	public function getNbResults() {
		return (! is_null ( $this->_nbResults ) ? $this->_nbResults : count ( $this->_results ));
	}

	/**
	 * Save LONG/LAT points for a city in a country
	 *
	 * @param string $city    City name
	 * @param string $country Country name
	 * @param string $postal  Zip code
	 *
	 * @return Array
	 */
	static private function _setInfos($city, $country, $postal = NULL) {
		$lws = new Twindoo_LocationService_Geonames_Xml ( );

		$lws->setLimit ( 10 );
		$lws->setCity ( $city );
		$lws->setCountry ( $country );
		if ($postal) {
            $lws->setPostal ( $postal );
        }
		$lws->setLocale ( "en" );
		$lws->run ();
		if ($lws->getNbResults ()) {
			$results = $lws->getResults ();
			return (Array ("LATITUDE" => $results [0] ['latitude'], "LONGITUDE" => $results [0] ['longitude'] ));
		} else {
			throw new Twindoo_LocationService_Geonames_Xml_Exception ('No result found !');
		}
	}
	
	/**
	 * Return LONG/LAT points for a city in a country
	 *
	 * @param string $city	City name
	 * @param string $country Country name
	 *
	 * @return Array
	 */
	static public function getInfos($city, $country, $postal = NULL) {
		return ( self::_setInfos ( $city, $country, $postal ) );
	}
}

class Twindoo_LocationService_Geonames_Xml_Exception extends Twindoo_LocationService_Exception {
}