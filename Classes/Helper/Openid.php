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
 * OpenID
 */
class Openid {
	protected $providerConf;
	protected $controller;

	public function init($providerConf, &$controller) {
		$this->providerConf = $providerConf;
		$this->controller = &$controller;
	}

	public function process() {
		return $this->redirToProvider();
	}

	protected function redirToProvider() {
		$error = null;
		$openid_url = $this->getOpenidUrl(trim($this->piVars['userName']), $error);
		if ($error) { return $error; }

		$openid = GeneralUtility::makeInstance('Dope_OpenID', $openid_url);
		$openid->setReturnURL($this->controller->getVerifyUrl());
		$trustRoot = GeneralUtility::locationHeaderUrl('/');
		$openid->SetTrustRoot($trustRoot);
		if ($this->providerConf['optionalInfo']) {
			$openid->setOptionalInfo(GeneralUtility::trimExplode(',', $this->providerConf['optionalInfo'])); // config
		}
		$openid->setRequiredInfo(GeneralUtility::trimExplode(',', $this->providerConf['requiredInfo'])); // config
		//$openid->setPapePolicies('http://schemas.openid.net/pape/policies/2007/06/phishing-resistant '); // config
		//$openid->setPapeMaxAuthAge(120); // config
		
		/*
		* Attempt to discover the user's OpenID provider endpoint
		*/
		$endpoint_url = $openid->getOpenIDEndpoint();
		if($endpoint_url){
			$openid->redirect();
		} else {
			$the_error = $openid->getError();
			$error = Div::getLL('error_endpoint', $the_error['code'], $the_error['description']); // Error while getting OpenID endpoint (%s): %s
		}
		return $error;
	}

	function getOpenidUrl($name, &$error) {
		$url = str_replace('###NAME###', $name, $this->providerConf['url']);
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			$error = Div::getLL('invalid_url', htmlspecialchars($url)); // Error: OpenID Identifier is not in proper format (%s).
		}
		return $url;
	}

	function verifyLogin() {
		if (GeneralUtility::_GP('openid_mode') == "cancel") { return; }
		
		$openid_id = GeneralUtility::_GP('openid_identity');
		$openid = GeneralUtility::makeInstance('Dope_OpenID', $openid_id);
		$validate_result = $openid->validateWithServer();
		if ($validate_result === TRUE) {

			$userinfo = $openid->filterUserInfo($_GET);
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['easylogin2']['hook_userInfo'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['easylogin2']['hook_userInfo'] as $_classRef) {
					$_procObj = &GeneralUtility::getUserObj($_classRef);
					$_procObj->process($userinfo, $_GET, $this);
				}
			}
			$userinfo['id'] = 'openid-'.$this->providerConf['key'].'-'.$openid_id;

			return $this->controller->loginFromIdentifier($userinfo['id'], $userinfo);
		} else if ($openid->isError() === TRUE){
			$the_error = $openid->getError();
			$error = Div::getLL('error_validate', $the_error['code'], $the_error['description']); // Error: Could not validate the OpenID
		} else {
			$error = Div::getLL('error_validate_openid_nocode', $openid_id); // Error: Could not validate the OpenID
		}
		return $error;
	}

}
