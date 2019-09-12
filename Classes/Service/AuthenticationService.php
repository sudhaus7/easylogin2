<?php
namespace DIX\Easylogin2\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;


class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService {

	/**
	 * true - this service was able to authenticate the user
	 */
	const STATUS_AUTHENTICATION_SUCCESS_CONTINUE = true;

	/**
	 * 200 - authenticated and no more checking needed
	 */
	const STATUS_AUTHENTICATION_SUCCESS_BREAK = 200;

	/**
	 * false - this service was the right one to authenticate the user but it failed
	 */
	const STATUS_AUTHENTICATION_FAILURE_BREAK = false;

	/**
	 * 100 - just go on. User is not authenticated but there's still no reason to stop
	 */
	const STATUS_AUTHENTICATION_FAILURE_CONTINUE = 100;



	/**
	 * Find a user (eg. look up the user record in database when a login is sent)
	 *
	 * @return mixed user array or false
	 * @throws UnsupportedLoginSecurityLevelException
	 */
	public function getUser() {
		$user = false;

		session_start();
		$identifier = $_SESSION['easylogin2_identifier'];
                        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(['xxx',$identifier]);exit;

		$_SESSION['easylogin2_identifier'] = null;

		$databaseConnection = $this->getDatabaseConnection();
		$table = 'tx_easylogin2_domain_model_identifiers';
		$quotedIdentifier = $databaseConnection->fullQuoteStr($identifier, $table);

		$res = $databaseConnection->exec_SELECTquery(
			'*',
			$table,
			sprintf("useridentifier=%s and deleted=0 and hidden=0", $quotedIdentifier)
		);
		$row = $databaseConnection->sql_fetch_assoc($res);
		

		$table = 'fe_users';

		$res = $databaseConnection->exec_SELECTquery(
			'*',
			$table,
			sprintf("uid=%d and deleted=0 and disable=0", $row['user'])
		);
		$user = $databaseConnection->sql_fetch_assoc($res);
		
		if ($user) { $user['fromIdentifier'] = true; }

		return $user;
	}

	/**
	 * Authenticates a user (Check various conditions for the user that might invalidate its
	 * authentication, e.g., password match, domain, IP, etc.).
	 *
	 * @param array $user Data of user.
	 * @return int|false
	 */
	public function authUser(array $user) {
		if ($user['fromIdentifier']) {
			return static::STATUS_AUTHENTICATION_SUCCESS_BREAK;
		} else {
			return static::STATUS_AUTHENTICATION_FAILURE_CONTINUE;
		}
	}


	/**
	 * Returns the database connection.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}


}
