<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\TagViewHelperTrait;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * ### Tag building ViewHelper
 *
 * Creates one HTML tag of any type, with various properties
 * like class and ID applied only if arguments are not empty,
 * rather than apply them always - empty or not - if provided.
 *
 * @package Vhs
 * @subpackage ViewHelpers
 */
class TagViewHelper extends AbstractTagBasedViewHelper {

	use TagViewHelperTrait;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerArgument('name', 'string', 'Tag name', TRUE);
	}

	/**
	 * @return string
	 */
	public function render() {
		$this->arguments['class'] = trim((string) $this->arguments['class']);
		$this->arguments['class'] = str_replace(',', ' ', $this->arguments['class']);
		$content = $this->renderChildren();
		return $this->renderTag($this->arguments['name'], $content);
	}

}
