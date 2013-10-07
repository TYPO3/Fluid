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
class Post {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var User
	 * @ORM\ManyToOne(cascade={"all"})
	 */
	protected $author;

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
	 * @param User $author
	 * @return void
	 */
	public function setAuthor(User $author) {
		$this->author = $author;
	}

	/**
	 * @return User
	 */
	public function getAuthor() {
		return $this->author;
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