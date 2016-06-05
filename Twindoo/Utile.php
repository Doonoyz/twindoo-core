<?php
/**
 * Collection of function usefull anywhere in the code
 *
 * @package   Core
 * @author    Jeremy MOULIN <jeremy.moulin@doonoyz.com>
 * @copyright 2008-2009 Doonoyz
 * @version   Paper
 */
class Twindoo_Utile {

	/**
	 * Prevent from HTML injections
	 *
	 * @param String|Array $results String or Array to securize
	 *
	 * @return String|Array Securized data
	 */
	static public function dbResultHtmlSecurise($results) {
		if (is_array($results)) {
			foreach ($results as $key => $val) {
				$results[$key] = self::dbResultHtmlSecurise($val);
			}
			return ($results);
		} else {
			return htmlentities($results);
		}
	}
	
	/**
	 * Clean HTML code by removing id and class attributes and JS injections
	 *
	 * @param string $string HTML Code to clean
	 * @return string Cleaned HTML code
	 */
	static public function cleanHtml($string) {
		$string = preg_replace ( "/id='[^']*'/u", "", $string );
		$string = preg_replace ( "/id=[\"][^\"]*[\"]/u", "", $string );
		$string = preg_replace ( "/class=[\"][^\"]*[\"]/u", "", $string );
		$string = preg_replace ( "/class='[^']*'/u", "", $string );
		$string = preg_replace ( "/id=[^ ]+/u", "", $string );
		$string = preg_replace ( "/class=[^ ]+/u", "", $string );
		$string = preg_replace ( "/<script.*>.*<\/script>/", "", $string );
		$string = preg_replace ( "/<script.*>/", "", $string );
		return ($string);
	}

	/**
	 * Remove all accents to their ANSI equivalent ex : "ü" => "u"
	 *
	 * @param string $string String containing accent
	 * @return string String without accent
	 */
	static public function removeAccent($string) {
		$Caracs = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A",
				"Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
				"Æ" => "AE", "Ç" => "C", "È" => "E", "É" => "E",
				"Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
				"Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N",
				"Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
				"Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U",
				"Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
				"à" => "a", "á" => "a", "â" => "a", "ã" => "a",
				"ä" => "a", "å" => "a", "æ" => "ae", "ç" => "c",
				"è" => "e", "é" => "e", "ê" => "e", "ë" => "e",
				"ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
				"ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o",
				"ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
				"ù" => "u", "ú" => "u", "û" => "u", "ü" => "u",
				"ý" => "y", "ÿ" => "y");

		return (strtr ( "$string", $Caracs ));
	}
	
	/**
	 * Decode a password encoded string
	 *
	 * @param string $password Password to decode
	 * @param string $str      String to decode
	 *
	 * @return string String password decoded
	 */
	static public function passwordDecode($password, $str) {
		$password = md5($password);
		$letter = -1;
		$newstr = '';
		$str = base64_decode($str);
		$strlen = strlen($str);
		for ( $i = 0; $i < $strlen; $i++ ) {
			$letter++;
			if ( $letter > 31 ) {
				$letter = 0;
			}
			$neword = ord($str{$i}) - ord($password{$letter});
			if ( $neword < 1 ) {
				$neword += 256;
			}
			$newstr .= chr($neword);
		}
		return $newstr;
	}

	/**
	 * Encode a string with a password
	 *
	 * @param string $password Password to use
	 * @param string $str      String to encode
	 *
	 * @return string String password encoded
	 */
	static public function passwordEncode($password, $str) {
		$password = md5($password);
		$letter = -1;
		$newstr = '';
		$strlen = strlen($str);
		for ( $i = 0; $i < $strlen; $i++ ) {
			$letter++;
			if ( $letter > 31 ) {
				$letter = 0;
			}
			$neword = ord($str{$i}) + ord($password{$letter});
			if ( $neword > 255 ) {
				$neword -= 256;
			}
			$newstr .= chr($neword);
		}
		return base64_encode($newstr);
	}
	
	/**
	 * Strtolower in UTF8
	 *
	 * @param string $inputString String to lower
	 *
	 * @return string String to lower
	 */
	static public function strtolower_utf8($inputString) {
		$outputString    = utf8_decode($inputString);
		$outputString    = strtolower($outputString);
		$outputString    = utf8_encode($outputString);
		return $outputString;
	}
	
	/**
	 * Strtoupper in UTF8
	 *
	 * @param string $inputString String to upper
	 *
	 * @return string String to upper
	 */
	static public function strtoupper_utf8($inputString) {
		$outputString    = utf8_decode($inputString);
		$outputString    = strtoupper($outputString);
		$outputString    = utf8_encode($outputString);
		return $outputString;
	}
	
	/**
	 * Set the session id for flash use
	 */
	static public function getFlashSession() {
		$credentials = Array('sessid' => Zend_Session::getId(), "secret" => uniqid());
		$value = self::passwordEncode('IKnowThisIsntSecureBecauseItsMySessionId', serialize($credentials));
		return ( urlencode ( $value ) );
	}
	
	/**
	 * Set the session id for flash use
	 */
	static public function setFlashSession() {
		if (isset($_GET['flashsession']) && strlen($_GET['flashsession'])) {
			try {
				$values = self::passwordDecode('IKnowThisIsntSecureBecauseItsMySessionId', $_GET['flashsession']);
				$values = unserialize($values);
				Zend_Session::setId($values['sessid']);
			} catch (Exception $e) {
			}
		}
	}
	
	/**
	 * Test if the IP is valid
	 *
	 * @param string $ip IP to test
	 *
	 * @return bool Valid IP ?
	 */
	private static function _testValidIp($ip) {
		if (!empty ( $ip ) && ip2long ( $ip ) != -1) {
			$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
			);

			foreach ($reserved_ips as $r) {
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long ( $ip ) >= $min) && ( ip2long ( $ip ) <= $max)) {
					return false;
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retrieve client IP, even behind a proxy
	 *
	 * @return string Client IP
	 */
	public static function getIp() {
		if (isset($_SERVER["HTTP_CLIENT_IP"]) && self::_testValidIp($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		}
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
				if ( self::_testValidIp ( trim ( $ip ) ) ) {
					return $ip;
				}
			}
		}
		if ( isset ($_SERVER["HTTP_X_FORWARDED"]) && self::_testValidIp ( $_SERVER["HTTP_X_FORWARDED"] )) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} elseif ( isset($_SERVER["HTTP_FORWARDED_FOR"]) && self::_testValidIp ($_SERVER["HTTP_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_FORWARDED_FOR"];
		} elseif ( isset($_SERVER["HTTP_FORWARDED"]) && self::_testValidIp ($_SERVER["HTTP_FORWARDED"])) {
			return $_SERVER["HTTP_FORWARDED"];
		} elseif ( isset($_SERVER["HTTP_X_FORWARDED"]) && self::_testValidIp ($_SERVER["HTTP_X_FORWARDED"])) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} else {
			return $_SERVER["REMOTE_ADDR"];
		}
	}
}
