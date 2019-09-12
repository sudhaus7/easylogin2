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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('easylogin2').'Resources/3rdParty/oauth/OAuth.php');

/**
 * OAuth 1.1
 */
class Oauth1 {
	protected $providerConf;
	protected $controller;

	public function init($providerConf, &$controller) {
		$this->providerConf = $providerConf;
		$this->controller = &$controller;

		$sigClass = 'OAuthSignatureMethod_'.trim(strtoupper($this->providerConf['sigMethod']));
		if (!class_exists($sigClass)) { $sigClass = 'OAuthSignatureMethod_HMAC_SHA1'; }
		$this->sigMethod = GeneralUtility::makeInstance($sigClass);
		$this->consumer = GeneralUtility::makeInstance('OAuthConsumer', $this->providerConf['consumerKey'], $this->providerConf['consumerSecret'], NULL);
	}
	
	public function process() {
		$error = $this->getRequestToken();
		if (!$error) {
			$error = $this->redirToProvider();
		}
		return $error;
	}

	function getRequestToken() {
		$req_req = \OAuthRequest::from_consumer_and_token($this->consumer, NULL, "GET", $this->providerConf['requestTokenUrl'], array());
		$req_req->set_parameter('oauth_callback', $this->controller->getVerifyUrl()); // xing expects this parameter when requesting the request token; twitter when redirecting to provider
		$req_req->sign_request($this->sigMethod, $this->consumer, NULL);

		$response = Div::makeCURLRequest((string)$req_req, 'GET', array());
		$params = array();
		parse_str($response, $params);
		$this->oauth_token = $params['oauth_token'];
		$this->oauth_token_secret = $params['oauth_token_secret'];
		if (!$this->oauth_token || !$this->oauth_token_secret) { return Div::getLL('error_reqToken', $response); }
		$this->controller->setKey("easylogin_oauth_token", $this->oauth_token);
		$this->controller->setKey("easylogin_oauth_token_secret", $this->oauth_token_secret);
	}

	function redirToProvider() { // authorize
		$callback_url = $this->controller->getVerifyUrl();
		$auth_url = $this->providerConf['authorizeUrl'] . '?oauth_token='.$this->oauth_token.'&oauth_callback='.urlencode($callback_url);
		\TYPO3\CMS\Core\Utility\HttpUtility::redirect($auth_url);
	}

	function verifyLogin() { // get access token
		$error = '';
		$this->oauth_token = $this->controller->getKey("easylogin_oauth_token");
		$this->oauth_token_secret = $this->controller->getKey("easylogin_oauth_token_secret");

		$tokenObj = GeneralUtility::makeInstance('OAuthConsumer', $this->oauth_token, $this->oauth_token_secret);
		$acc_req = \OAuthRequest::from_consumer_and_token($this->consumer, $tokenObj, "GET", $this->providerConf['accessTokenUrl'], array());
		if ($verifier = GeneralUtility::_GP('oauth_verifier')) {
			$acc_req->set_parameter('oauth_verifier', $verifier); // xing expects this parameter
		}
		$acc_req->sign_request($this->sigMethod, $this->consumer, $tokenObj);

		$response = Div::makeCURLRequest((string)$acc_req, 'GET', array());

		$params = array();
		parse_str($response, $params);
		// problem here: according to oauth specs there is no need for a response parameter identifing the user. 
		// twitter uses "user_id" but other oauth providers may use "userid", "uid", "user", "id" or worst: nothing at all
		if (!$params['oauth_token']) {
			$error = Div::getLL('error_getting_accesstoken', $response); // Error: Could not get access token (%s)
			return $error; 
		}
		$this->oauth_token = $params['oauth_token'];
		$this->oauth_token_secret = $params['oauth_token_secret'];
		
		$userinfo = $this->getUserInfo($params, $error);
		if ($error) { return $error; }
		return $this->controller->loginFromIdentifier($userinfo['id'], $userinfo);
	}

	function getUserInfo($accessTokenParams, &$error) {
		$endpoint = $this->providerConf['requestProfileUrl'];
		$markerNames = $this->extractMarker($endpoint);
		foreach ($markerNames as $v) {
			$endpoint = str_replace('###'.$v.'###', $accessTokenParams[$v], $endpoint);
		}
		$tokenObj = GeneralUtility::makeInstance('OAuthConsumer', $this->oauth_token, $this->oauth_token_secret);
		$req = \OAuthRequest::from_consumer_and_token($this->consumer, $tokenObj, "GET", $endpoint, array());
		$req->sign_request($this->sigMethod, $this->consumer, $tokenObj);

		$response = Div::makeCURLRequest((string)$req, 'GET', array());
		$details = json_decode($response, true);
		if ($details['users']) { $details = $details['users']; } // when the details are stored in an object capsulated in an array capsulated in an object (xing)
		if ($details[0]) { $details = $details[0]; } // when the details are stored in an object capsulated in an array (twitter)
		$userinfo = array();
		foreach ($this->providerConf['profileMap'] as $dbField => $detailsField) {
			$userinfo[$dbField] = $details[$detailsField];
		}
		if (!$userinfo['id']) {
			$error = Div::getLL('error_getting_userinfo'); // Error: While retrieving user details, the user id was empty
		}
		$userinfo['id'] = 'oauth1-'.$this->providerConf['key'].'-'.$userinfo['id'];

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['easylogin2']['hook_userInfo'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['easylogin2']['hook_userInfo'] as $_classRef) {
				$_procObj = & GeneralUtility::getUserObj($_classRef);
				$_procObj->process($userinfo, $details, $this);
			}
		}

		return $userinfo;
	}
	
	function extractMarker($str) {
		$result = array();
		while (strpos($str, '###') !== false) {
			$start = strpos($str, '###') + 3;
			$stop = strpos($str, '###', $start);
			$result[] = substr($str, $start, $stop-strlen($str));
			$str = substr($str, $stop+3);
		}
		return $result;
	}


}