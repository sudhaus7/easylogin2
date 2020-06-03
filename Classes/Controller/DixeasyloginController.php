<?php
namespace DIX\Easylogin2\Controller;

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
 * DixeasyloginController
 */
class DixeasyloginController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Object Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * identifiersRepository
	 *
	 * @var \DIX\Easylogin2\Domain\Repository\IdentifiersRepository
	 * @inject
	 */
	protected $identifiersRepository = NULL;

	/**
	 * userRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $userRepository = NULL;

	/**
	 * groupRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	protected $groupRepository = NULL;

	/**
	 * persistanceManager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistanceManager;
		
	private $providers;
	
	public function verifyAction() {
		$loginType = $this->getKey("easylogin_loginType");
		$authObj = $this->initAuthObj($loginType);

		if ($authObj) {
			$success = $authObj->verifyLogin();
		}
		if ($success === false) {
			$this->addFlashMessage('Error', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
		} elseif (is_string($success)) {
			$this->addFlashMessage($success, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
		}
		$this->forward('index'); // just in case of error - then the preserveVars aren't preserved
	}
	
	/**
	 * action providerselected
	 *
	 * @param \DIX\Easylogin2\Domain\Model\Thedata $thedata
	 * @return void
	 */
	public function providerselectedAction(\DIX\Easylogin2\Domain\Model\Thedata $thedata) {
		$loginType = $thedata->getLoginType();
		$this->setKey("easylogin_loginType", $loginType);
		$authObj = $this->initAuthObj($loginType);
		if ($authObj) { 
			$error = $authObj->process();
			if ($error) {
				$this->addFlashMessage($error, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
			}
		}
		$this->forward('index'); // just in case of error - then the preserveVars aren't preserved
	}

	public function initializeAction() {
		session_start();
		if ($GLOBALS["TSFE"] && $GLOBALS["TSFE"]->fe_user) {

			//$GLOBALS["TSFE"]->fe_user->fetchSessionData();
		}
		$this->providers = $this->getProvider();
	}


	private function initAuthObj($loginType) {
 		if (!$this->providers[$loginType]) { return null; }
		
		$class = 'DIX\\Easylogin2\\Helper\\' . ucfirst(strtolower($this->providers[$loginType]['type']));
		
		$authObj = $this->objectManager->get($class);
		if ($authObj) { 
			$authObj->init($this->providers[$loginType], $this);
		}
		return $authObj;
	}

	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {
		$values = array(
			'provider' => $this->providers,
			'anchorPrefix' => GeneralUtility::getIndpEnv('REQUEST_URI'),
			'user' => $GLOBALS['TSFE']->fe_user->user,
			'constants' => array('CONTENTELEMENT' => 'CONTENTELEMENT'),
			'associated' => $this->getAssociatedProvider($GLOBALS['TSFE']->fe_user->user['uid']),
			'verifyUrl' => $this->getVerifyUrl(true),
		);
		
		$this->view->assign('values', $values);
	}
	
	protected function getSelfUrl() {
		$ub = $this->uriBuilder;
		$preservedVars = $this->getKey('easylogin_preservedVars');
		$ub->reset();
		$ub->setCreateAbsoluteUri(false)->setUseCacheHash(false); // http://stackoverflow.com/questions/34255934/typo3-uribuilder-and-realurl-link/
		$ub->setArguments($preservedVars);
		$link = $ub->build();
		$uri = GeneralUtility::locationHeaderUrl($link);
		return $uri;
	}

	protected function getLoginUrl() {
		$ub = $this->uriBuilder;
		$preservedVars = (array)$this->getKey('easylogin_preservedVars');
		$queryString = array_merge_recursive($preservedVars, array('logintype' => 'login')); // params overrules preservedVars
		$ub->reset();
		$ub->setCreateAbsoluteUri(false)->setUseCacheHash(false); // http://stackoverflow.com/questions/34255934/typo3-uribuilder-and-realurl-link/
		$ub->setArguments($queryString);
		$link = $ub->build();
		$uri = GeneralUtility::locationHeaderUrl($link);
		return $uri;
	}
	
	public function getVerifyUrl($save=false) { // save the preservedVars, so that a later redirect will contain them (but not the verify url, since some authentication providers except only some urls)
	    
	    //$ar = explode('?',$_SERVER['REQUEST_URI']);
	    
	    //return (isset($_SERVER['HTTPS'])&&!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$ar[0];
	    
	    
		if ($save) {
			$this->setKey('easylogin_preservedVars', $this->getPreservedVars());
		}

		$ub = $this->uriBuilder;
		$ub->reset();
		$ub->setCreateAbsoluteUri(false)->setUseCacheHash(false); // http://stackoverflow.com/questions/34255934/typo3-uribuilder-and-realurl-link/

		if ($this->settings['pid_verify']) {
			$ub->setTargetPageUid($this->settings['pid_verify']);
		}
		$ub->uriFor('verify');

		$link = $ub->build();
		$uri = GeneralUtility::locationHeaderUrl($link);
		return $uri;
	}

	protected function getPreservedVars() {
		$getVars = GeneralUtility::_GET();
		$conf = $this->settings['preserveGETvars'];
		if ('all' == $conf) { return $getVars; }
		$conf = strtr($conf, '&?= ', ''); // basic validation
		$conf = str_replace(',', '=1&', $conf).'=1'; // transform to url style
		parse_str($conf, $params);
		$keep = Div::array_intersect_key_recursive((array)$getVars, (array)$params);
		return $keep;
	}

	private function getAssociatedProvider($uid) {
		$result = array();
		$res = $this->identifiersRepository->findByUser($uid);
		foreach ($res as $row) {
			$result[$row->getConnectiontype()] = $row;
		}
		return $result;
	}

	public function initializeIndexAction() {
		if (!function_exists('curl_exec')) {
			die('Error: easylogin requires the PHP cURL extension.');
		}
	}

	private function getProvider() {
		$result = array();
		foreach ($this->settings['provider'] as $key=>$conf) {
			$conf['type'] = trim(strtoupper($conf['_typoScriptNodeValue']));
			$conf['key'] = $key;
			$conf['icon'] = $conf['icon'] ? Div::getFileRelFileName($conf['icon']) : '';
			$conf['showMe'] = (!(bool)$GLOBALS['TSFE']->fe_user->user['uid'] || $conf['showWhenLoggedIn']);
			$conf['withUsername'] = (bool) (strstr($conf['url'], '###NAME###')); // only openid
			$result[$key] = $conf;
		}
		return $result;
	}

	/**
	 * Tries to log in user into TYPO3 front-end by checking if the ID provided by the external 
	 * auth system matches a record in fe_users in the field tx_dixeasylogin_openid
	 * If configured so, it will create a user or connect a logged-in user with the given identifier
	 * 	 
	 * @param	 string $identifier		The identifier as provided by Facebook or other systems. 
	 * @return	bool	 (success / error)
	 */
	public function loginFromIdentifier($identifier, $userinfo) {
		$user = $this->fetchUserByIdentifier($identifier);
		$fe_user = $GLOBALS['TSFE']->fe_user->fetchUserSession();
		if ($fe_user['uid']) { // user already logged in -> try to update the identifier
			if ($this->settings['allowUpdate']) {
				$this->linkIdentifier2User($identifier, (int)$fe_user['uid']);
				$this->setKey("easylogin_loginType","");
				//$this->addFlashMessage(Div::getLL('connect_success'));
				\TYPO3\CMS\Core\Utility\HttpUtility::redirect($this->getSelfUrl());
				return true;
			}
			return true; // should never be reached
		}

		// from this point on we are sure that the user is not logged in yet
		if (!$user && $this->settings['allowCreate']) {
			if (Div::checkMailDomain($userinfo['email'], $this->settings['trustedDomains'])) {
				try {
					$user = $this->createUser($identifier, $userinfo);
					$this->addFlashMessage(Div::getLL('user_created'));
				} catch (\RuntimeException $e) {
					$this->addFlashMessage(Div::getLL($e->getMessage()), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
					return false;
				}
			}

		}
		if ($user) {
			$_SESSION['easylogin2_identifier'] = $identifier;
			$this->addFlashMessage(Div::getLL('login', $identifier));
			\TYPO3\CMS\Core\Utility\HttpUtility::redirect($this->getLoginUrl());
		} else {
			$this->setKey("easylogin_loginType","");
			$this->addFlashMessage(Div::getLL('nouser', $identifier), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
			return false; // User not found. Please contact the admin of the website to request access to this site. Tell the admin this identifier: %s
		}
	}

	/**
	* @param string $identifier Identifier provided by the authorization mechanism e.g facebook-ID 
	* @return array corresponding fe_user record
	*/
	protected function fetchUserByIdentifier($identifier) {
		$idObjs = $this->identifiersRepository->findByUseridentifier($identifier);
		$idObj = $idObjs ? $idObjs->getFirst() : null;
		return $idObj ? $idObj->getUser() : null;
	}

	protected function createUser($identifier, $userinfo) {
		$userObj = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser::class);
		if ($userinfo['email']) { // doesn't come with twitter e.g.
			$tmpObj = $this->userRepository->findOneByEmail($userinfo['email']);
			if ($tmpObj && !$this->settings['allowMigrate']) {
				throw new \RuntimeException('email_exists');
			}
			$userObj = $tmpObj;
		}
		$userObj->setEmail($userinfo['email']);
		$userObj->setUsername($this->normalizeUserName($userinfo['nickname'] ? $userinfo['nickname'] : $userinfo['email']));
		$userObj->setPassword(GeneralUtility::getRandomHexString(32));
		$userObj->setName($userinfo['fullname'] ? $userinfo['fullname'] : trim($userinfo['firstname'].' '.$userinfo['lastname'].' '.$userinfo['suffix']));
		$userObj->setTitle($userinfo['prefix']);
		$userObj->setFirstName($userinfo['firstname']);
		$userObj->setLastName($userinfo['lastname']);
		$userObj->setZip($userinfo['postcode']);
		$userObj->setCountry($userinfo['country']);
		$groupObj = $this->groupRepository->findByUid($this->settings['usergroup']);
		$userObj->addUsergroup($groupObj);

		if (!trim($userObj->getName())) { $userObj->setName($userinfo['nickname']); }
		$this->userRepository->add($userObj);

		$idObj = $this->objectManager->get('DIX\\Easylogin2\\Domain\\Model\\Identifiers');
		$type = $this->getKey("easylogin_loginType");
		$idObj->setConnectiontype($type);
		$idObj->setConnectionname($this->settings['provider'][$type]['name']);
		$idObj->setUser($userObj);
		$idObj->setUseridentifier($identifier);
		$this->identifiersRepository->add($idObj);

		/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
		$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
		$signalSlotDispatcher->dispatch(__CLASS__, 'afterUserCreate', array($userObj, $userinfo));

		$this->persistanceManager->persistAll();
		
		$this->sendNotificationMail($userObj);

		return $userObj;
	}
	
	protected function sendNotificationMail($userObj) {
		if (!$this->settings['mailAdminOnCreate']) { return; }
		$mail = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$message = Div::getLL('email_body', $userObj->getUsername(), $userObj->getEmail());
		$result = $mail
			->setFrom($this->settings['mailAdminFrom'])
			->setTo($this->settings['mailAdminTo'])
			->setSubject(Div::getLL('email_subject'))
			->setBody($message, 'text/plain') // defaults to utf-8
			->send();
	}
	
	protected function linkIdentifier2User($identifier, $uid) {
		$idObjs = $this->identifiersRepository->findByUseridentifier($identifier);
		$idObj = $idObjs ? $idObjs->getFirst() : null;
		$userObj = $this->userRepository->findByUid($uid);
		$type = $this->getKey("easylogin_loginType");
		if (!$idObj) {
			$idObj = $this->objectManager->get('DIX\\Easylogin2\\Domain\\Model\\Identifiers');
			$idObj->setUseridentifier($identifier);
		}
		$idObj->setConnectiontype($type);
		$idObj->setConnectionname($this->settings['provider'][$type]['name']);
		$idObj->setUser($userObj);
		if ($idObj->getUid()) {
			$this->identifiersRepository->update($idObj);
		} else {
			$this->identifiersRepository->add($idObj);
		}
		$this->persistanceManager->persistAll();
	}
	
	// lower, nospace, uniqueInPid
	protected function normalizeUserName($name) {
		$name = str_replace(' ', '', strtolower($name));
		$dataHandler = $this->objectManager->get("TYPO3\\CMS\\Core\\DataHandling\\DataHandler");
		return $dataHandler->getUnique('fe_users', 'username', $name, 0, $this->settings['user_pid']);
	}
	
	public function getKey($key) {
	    return isset($_SESSION['easylogin2_'.$key]) ? $_SESSION['easylogin2_'.$key] : $GLOBALS["TSFE"]->fe_user->getKey("ses", $key);
	
	}
	public function setKey($key, $value) {
		$GLOBALS["TSFE"]->fe_user->setKey("ses", $key, $value);
		$GLOBALS["TSFE"]->fe_user->storeSessionData();
        $_SESSION['easylogin2_'.$key]=$value;
	}

}
