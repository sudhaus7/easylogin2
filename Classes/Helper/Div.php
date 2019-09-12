<?php
namespace DIX\Easylogin2\Helper;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Markus Kappe <markus.kappe@dix.at>, DIX web.solutions
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Div
 */
class Div implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Checks if the E-Mail-Domain (the part after the @ sign) is within the trusted domains. * is a wildcard 
	 * 	 
	 * @param   string $email    The E-Mail-Address to be checked, e.g. me@example.com 
	 * @param   string $domains  Valid domains that allow creation of user accounts. e.g. "gmail.com, yahoo.de", "*", "*.mycompany.com" 
	 * @return  bool   true if allowed, false otherwise
	 */
	static public function checkMailDomain($email, $domains) {
		if ($domains == '*') { return true; }
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { return false; }
		$mailDomain = substr($email, strpos($email, '@')+1);
		$domainArray = GeneralUtility::trimExplode(',', $domains);
		foreach ($domainArray as $domain) {
			$starpos = strpos($domain, '*');
			if ($starpos !== false) {
				$part1 = substr($domain, $starpos+1);
				$part2 = substr($mailDomain, -strlen($part1));
				if ($part1 === $part2) { return true; }
			} else {
				if ($domain === $mailDomain) { return true; }
			}
		}
		return false;
	}


	// recursive
	static function array_intersect_key_recursive(array $arr1, array $arr2) {
		$result = array();
		foreach ($arr1 as $k1=>$v1) {
			if (!isset($arr2[$k1])) { continue; }
			if (is_array($v1) && is_array($arr2[$k1])) {
				if ($merged = self::array_intersect_key_recursive($v1, $arr2[$k1])) {
					$result[$k1] = $merged;
				}
			} elseif (!is_array($arr2[$k1])) {
				$result[$k1] = $v1;
			}
		}
		return $result;
	}
	

	static public function makeCURLRequest($url, $method="GET", $params = "") {
		if ($method == "GET" && strpos($url, '?')) {
			$urlParams = substr($url, strpos($url, '?')+1);
			$url = substr($url, 0, strpos($url, '?'));
			if (is_array($params)) {
				parse_str($urlParams, $urlParamsArray);
				$params = $urlParamsArray + $params;
			} else { // $params is string
				$params = $urlParams.'&'.$params;
			}
		}
		if (is_array($params)) {
			$params = http_build_query($params,'','&');
		}
		$curl = curl_init($url . ($method == "GET" && $params != "" ? "?" . $params : ""));
		
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HTTPGET, ($method == "GET"));
		curl_setopt($curl, CURLOPT_POST, ($method == "POST"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		if ($method == "POST") {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		}
		
		$response = curl_exec($curl);
		return $response;
	}

	static public function getFileRelFileName($filename) {
		if (substr($filename, 0, 4) == 'EXT:') { // extension
			list($extKey, $local) = explode('/', substr($filename, 4), 2);
			$filename = '';
			if (strcmp($extKey, '') && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey) && strcmp($local, '')) {
				$filename = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($extKey) . $local;
			}
		}
		return $filename;
	}
	
	static public function getLL($key) {
		$str = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'easylogin2');
		if (func_num_args() > 1) {
			$arg_list = func_get_args();
			array_shift($arg_list);
			$result = vsprintf($str, $arg_list);
		} else {
			$result = $str;
		}
		return $result;
	}

}