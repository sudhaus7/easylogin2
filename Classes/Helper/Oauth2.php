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
use DIX\Easylogin2\Helper\Div;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * OAuth 2.0
 */
class Oauth2 {
	protected $providerConf;
	protected $controller;

	public function init($providerConf, &$controller) {
		$this->providerConf = $providerConf;
		$this->controller = &$controller;
	}
	
	public function process() {
		return $this->redirToProvider();
	}
	
	protected function redirToProvider() { // authorize
		$state = uniqid('', true); // unique long string
		$this->controller->setKey("easylogin_oauth2_state", $state);
		$verifyUrl = $this->controller->getVerifyUrl();
		
		if (strpos($verifyUrl, '?')) {
			//throw new \Exception(Div::getLL('qmark_in_url', $verifyUrl));
		}

		$params = array(
			'response_type' => 'code',
			'client_id' => $this->providerConf['consumerKey'],
			//'scope' => $this->providerConf['scope'],
			'state' => $state,
			'redirect_uri' => $verifyUrl,
		);
		$auth_url = $this->providerConf['authorizeUrl'] . '&' . http_build_query($params,'','&');
		$username = '';
		//echo '<pre>';print_r([$username,$_POST,$auth_url]);exit;
		if (isset($_POST['tx_easylogin2_easylogin']['thedata']) && isset($_POST['tx_easylogin2_easylogin']['thedata']['userName'])) {
		    
            $username = trim($_POST['tx_easylogin2_easylogin']['thedata']['userName']);
            
        }
        $auth_url = str_replace('###NAME###',$username,$auth_url);
		\TYPO3\CMS\Core\Utility\HttpUtility::redirect($auth_url);
	}

	function verifyLogin() {
		$state = $this->controller->getKey("easylogin_oauth2_state");
		if ($state != GeneralUtility::_GET('state')) {
			throw new \Exception('State parameter mismatch: either session timed out or XSRF attack');
		}

		$error = '';
		$token = $this->getToken(GeneralUtility::_GET('code'), $error);
		if ($error) { return $error; }
		$userinfo = $this->getUserInfo($token, $error);
		if ($error) { return $error; }
		return $this->controller->loginFromIdentifier($userinfo['id'], $userinfo);
	}

	function getToken($code, &$error) {
		$config  = array(
                        'grant_type' => 'authorization_code',
                        'client_id' => $this->providerConf['consumerKey'],
                        'redirect_uri' => $this->controller->getVerifyUrl(), // must not contain a question mark "?"
                        'client_secret' => $this->providerConf['consumerSecret'],
                        'code' => $code,
                );
		$response = Div::makeCURLRequest($this->providerConf['accessTokenUrl'], 'POST', $config);
		if (!$response) {
			$error = Div::getLL('error_validate_nocode', $code); // Error while validating code-parameter '%s' (no answer)
			return false;
		}
		$decoded = json_decode($response, true);
		if ($decoded['error']) {
			$error = Div::getLL('error_oauth2_validate', print_r($decoded, TRUE)); // Error while validating code-parameter (%s)
			return false;
		}
		if ($decoded['access_token']) { return $decoded['access_token']; }
		// else try url parsing
		$result = array();
		parse_str($response, $result);
		if (!$result['access_token']) {
			$error = Div::getLL('error_token'); // Error: could not retrieve access_token
		}
		return $result['access_token'];
	}
	
	/**
	 * recursive function that reduces multi-level-arrays to one single level
	 * e.g. 
	 * $input = array('name' => array('first' => 'Markus', 'last' => 'Kappe'));
	 * transforms into
	 * $result = array('name.first' => 'Markus', 'name.last' => 'Kappe');
	 */
	function flattenArray($input, $prefix='') {
		if (!is_array($input)) { return $input; }
		$result = array();
		foreach ($input as $key=>$value) {
			if (is_array($value)) {
				$result = $result + $this->flattenArray($value, $prefix.$key.'.');
			} else {
				$result[$prefix.$key] = $value;
			}
		}
		return $result;
	}

	function getUserInfo($token, &$error) {
		$userinfo = array();
		$response = Div::makeCURLRequest($this->providerConf['requestProfileUrl'], 'GET', array('access_token' => $token, 'oauth2_access_token' => $token)); // linkedin uses oauth2_access_token; facebook parameter is named access_token
		if (strtolower(trim($this->providerConf['profileEncoding'])) == 'json') {
			$decoded = (array)json_decode($response, true);
/*
			if (!$decoded['error']) {
				$decoded = $this->flattenArray($decoded); // previously needed for google
			}
*/
		} elseif (strtolower(trim($this->providerConf['profileEncoding'])) == 'xml') {
			$decoded = array();
			$decoded_step1 = (array)simplexml_load_string($response);
			foreach ($decoded_step1 as $k=>$v) {
				$decoded[$k] = is_object($v) ? (array)$v : $v; // second level simplxml data
			}
		} else { // url encoded
			$decoded = array(); // relevant? not until now...
		}
		if ($decoded['error']) {
			$error = Div::getLL('error_validate_token', $decoded['error']['type'], $decoded['error']['message']); // Error while validating token-parameter (%s: %s)
			return;
		}
		foreach ($this->providerConf['profileMap'] as $dbField => $detailsField) {
			$userinfo[$dbField] = $decoded[$detailsField];
		}

		if (!$userinfo['id']) {
			$error = Div::getLL('error_getting_userinfo'); // Error: While retrieving user details, the user id was empty
		}
		$userinfo['id'] = 'oauth2-'.$this->providerConf['key'].'-'.$userinfo['id'];

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['easylogin2']['hook_userInfo'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['easylogin2']['hook_userInfo'] as $_classRef) {
				$_procObj = GeneralUtility::getUserObj($_classRef);
				$_procObj->process($userinfo, $decoded, $this);
			}
		}

		return $userinfo;
	}


}
