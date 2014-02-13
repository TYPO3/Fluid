<?php
namespace TYPO3\Fluid\Core\ViewHelper;

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
use TYPO3\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3\Flow\I18n;

/**
 * Abstract view helper with locale awareness.
 *
 * @api
 */
abstract class AbstractLocaleAwareViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Service
	 */
	protected $localizationService;

	/**
	 * Constructor
	 *
	 * @api
	 */
	public function __construct() {
		$this->registerArgument('forceLocale', 'mixed', 'Whether if, and what, Locale should be used. May be boolean, string or \TYPO3\Flow\I18n\Locale', FALSE);
	}

	/**
	 * Get the locale to use for all locale specific functionality.
	 *
	 * @throws InvalidVariableException
	 * @return I18n\Locale The locale to use or NULL if locale should not be used
	 */
	protected function getLocale() {
		if (!$this->hasArgument('forceLocale')) {
			return NULL;
		}
		$forceLocale = $this->arguments['forceLocale'];
		$useLocale = NULL;
		if ($forceLocale instanceof I18n\Locale) {
			$useLocale = $forceLocale;
		} elseif (is_string($forceLocale)) {
			try {
				$useLocale = new I18n\Locale($forceLocale);
			} catch (I18n\Exception $exception) {
				throw new InvalidVariableException('"' . $forceLocale . '" is not a valid locale identifier.', 1342610148, $exception);
			}
		} elseif ($forceLocale === TRUE) {
			$useLocale = $this->localizationService->getConfiguration()->getCurrentLocale();
		}

		return $useLocale;
	}
}

?>