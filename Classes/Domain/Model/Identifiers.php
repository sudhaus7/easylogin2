<?php
namespace DIX\Easylogin2\Domain\Model;


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

/**
 * Identifiers
 */
class Identifiers extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * connectionname
	 *
	 * @var string
	 */
	protected $connectionname = '';
	
	/**
	 * connectiontype
	 *
	 * @var string
	 */
	protected $connectiontype = '';
	
	/**
	 * useridentifier
	 *
	 * @var string
	 */
	protected $useridentifier = '';
	
	/**
	 * user
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 */
	protected $user;
	

	/**
	 * Returns the connectionname
	 *
	 * @return string $connectionname
	 */
	public function getConnectionname() {
		return $this->connectionname;
	}
	
	/**
	 * Sets the connectionname
	 *
	 * @param string $connectionname
	 * @return void
	 */
	public function setConnectionname($connectionname) {
		$this->connectionname = $connectionname;
	}
	
	/**
	 * Returns the connectiontype
	 *
	 * @return string $connectiontype
	 */
	public function getConnectiontype() {
		return $this->connectiontype;
	}
	
	/**
	 * Sets the connectiontype
	 *
	 * @param string $connectiontype
	 * @return void
	 */
	public function setConnectiontype($connectiontype) {
		$this->connectiontype = $connectiontype;
	}
	
	/**
	 * Returns the useridentifier
	 *
	 * @return string $useridentifier
	 */
	public function getUseridentifier() {
		return $this->useridentifier;
	}
	
	/**
	 * Sets the useridentifier
	 *
	 * @param string $useridentifier
	 * @return void
	 */
	public function setUseridentifier($useridentifier) {
		$this->useridentifier = $useridentifier;
	}
	
	
	/**
	 * Returns the user
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Sets the user
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
	 * @return void
	 */
	public function setUser(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user) {
		$this->user = $user;
	}

}