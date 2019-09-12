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
 * Thedata
 */
class Thedata extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	
	/**
	 * loginType
	 *
	 * @var integer
	 */
	protected $loginType = 0;
	
	/**
	 * process
	 *
	 * @var boolean
	 */
	protected $process = false;
	
	/**
	 * userName
	 *
	 * @var string
	 */
	protected $userName = '';
	
	
	/**
	 * Returns the loginType
	 *
	 * @return integer $loginType
	 */
	public function getLoginType() {
		return $this->loginType;
	}
	
	/**
	 * Sets the loginType
	 *
	 * @param integer $loginType
	 * @return void
	 */
	public function setLoginType($loginType) {
		$this->loginType = $loginType;
	}
	
	/**
	 * Returns the process
	 *
	 * @return boolean $process
	 */
	public function getProcess() {
		return $this->process;
	}
	
	/**
	 * Sets the process
	 *
	 * @param boolean $process
	 * @return void
	 */
	public function setProcess($process) {
		$this->process = $process;
	}
	
	/**
	 * Returns the userName
	 *
	 * @return string $userName
	 */
	public function getUserName() {
		return $this->userName;
	}
	
	/**
	 * Sets the userName
	 *
	 * @param string $userName
	 * @return void
	 */
	public function setUserName($userName) {
		$this->userName = $userName;
	}
}
