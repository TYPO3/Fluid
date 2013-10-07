<?php
namespace TYPO3\Fluid\Tests\Functional\Form\Fixtures\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A test entity which is used to test Fluid forms in combination with
 * property mapping
 *
 * @Flow\Entity
 */
class User {

	/**
	 * @var string
	 * @Flow\Validate(type="EmailAddress")
	 */
	protected $emailAddress;

	/**
	 * @return string
	 */
	public function getEmailAddress() {
		return $this->emailAddress;
	}

	/**
	 * @param string $email
	 */
	public function setEmailAddress($email) {
		$this->emailAddress = $email;
	}

}