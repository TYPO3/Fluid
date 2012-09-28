<?php
namespace TYPO3\Fluid\Tests\Functional\Form\Fixtures\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
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
class Post {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 * @Flow\Validate(type="EmailAddress")
	 */
	protected $email;

	/**
	 * @var boolean
	 * @ORM\Column(nullable=true)
	 */
	protected $private;

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $category;

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $subCategory;

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @param boolean $private
	 */
	public function setPrivate($private) {
		$this->private = $private;
	}

	/**
	 * @return boolean
	 */
	public function getPrivate() {
		return $this->private;
	}

	/**
	 * @param string $category
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

	/**
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @param string $subCategory
	 */
	public function setSubCategory($subCategory) {
		$this->subCategory = $subCategory;
	}

	/**
	 * @return string
	 */
	public function getSubCategory() {
		return $this->subCategory;
	}
}