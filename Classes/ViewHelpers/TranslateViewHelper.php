<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Returns translated message using source message or key ID.
 *
 * Also replaces all placeholders with formatted versions of provided values.
 *
 * = Examples =
 *
 * <code title="Default parameters">
 * <f:translate>Untranslated label</f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated label"
 * </output>
 *
 * <code title="Custom source and locale">
 * <f:translate source="SomeLabelsCatalog" locale="de_DE">Untranslated label</f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated label" from custom source "SomeLabelsCatalog" and locale "de_DE"
 * </output>
 *
 * <code title="Custom source from other package">
 * <f:translate source="LabelsCatalog" package="OtherPackage">Untranslated label</f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated label" from custom source "LabelsCatalog" in "OtherPackage"
 * </output>
 *
 * <code title="Arguments">
 * <f:translate arguments="{0: 'foo', 1: '99.9'}">Untranslated {0} and {1,number}</f:translate>
 * </code>
 * <output>
 * translation of the label "Untranslated foo and 99.9"
 * </output>
 *
 * <code title="Translation by id">
 * <f:translate key="user.unregistered">Unregistered User</f:translate>
 * </code>
 * <output>
 * translation of label with the id "user.unregistered" and the default translation "Unregistered User"
 * </output>
 *
 * <code title="Inline notation">
 * {f:translate(key: 'someLabelId', default: 'default translation')}
 * </code>
 * <output>
 * translation of label with the id "someLabelId" and the default translation "default translation"
 * </output>
 *
 */
class TranslateViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\FLOW3\I18n\Translator
	 */
	protected $translator;

	/**
	 * @param \TYPO3\FLOW3\I18n\Translator $translator
	 * @return void
	 */
	public function injectTranslator(\TYPO3\FLOW3\I18n\Translator $translator) {
		$this->translator = $translator;
	}

	/**
	 * Renders the translated label.
	 *
	 * Replaces all placeholders with corresponding values if they exist in the
	 * translated label.
	 *
	 * @param string $key Id to use for finding translation
	 * @param array $arguments Numerically indexed array of values to be inserted into placeholders
	 * @param string $default if $key is not specified or could not be resolved, this value is used. If this argument is not set, child nodes will be used to render the default
	 * @param array $arguments Numerically indexed array of values to be inserted into placeholders
	 * @param string $source Name of file with translations
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
	 * @param string $locale An identifier of locale to use (NULL for use the default locale)
	 * @return string Translated label or source label / ID key
	 */
	public function render($key = NULL, $default = NULL, array $arguments = array(), $source = 'Main', $package = NULL, $quantity = NULL, $locale = NULL) {
		$localeObject = NULL;
		if ($locale !== NULL) {
			try {
				$localeObject = new \TYPO3\FLOW3\I18n\Locale($locale);
			} catch (\TYPO3\FLOW3\I18n\Exception\InvalidLocaleIdentifierException $e) {
				throw new \TYPO3\Fluid\Core\ViewHelper\Exception('"' . $locale . '" is not a valid locale identifier.' , 1279815885);
			}
		}
		if ($package === NULL) $package = $this->controllerContext->getRequest()->getControllerPackageKey();
		$originalLabel = $default !== NULL ? $default : $this->renderChildren();
		$quantity = $quantity === NULL ? NULL : (integer)$quantity;

		if ($key === NULL) {
			return $this->translator->translateByOriginalLabel($originalLabel, $arguments, $quantity, $localeObject, $source, $package);
		} else {
			$translation = $this->translator->translateById($key, $arguments, $quantity, $localeObject, $source, $package);
			if ($translation === $key) {
				return $originalLabel;
			} else {
				return $translation;
			}
		}
	}
}

?>
