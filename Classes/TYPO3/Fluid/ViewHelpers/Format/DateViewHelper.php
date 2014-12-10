<?php
namespace TYPO3\Fluid\ViewHelpers\Format;

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
use TYPO3\Flow\I18n\Exception as I18nException;
use TYPO3\Flow\I18n\Formatter\DatetimeFormatter;
use TYPO3\Fluid\Core\ViewHelper\AbstractLocaleAwareViewHelper;
use TYPO3\Fluid\Core\ViewHelper\Exception as ViewHelperException;

/**
 * Formats a \DateTime object.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:format.date>{dateObject}</f:format.date>
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the current date)
 * </output>
 *
 * <code title="Custom date format">
 * <f:format.date format="H:i">{dateObject}</f:format.date>
 * </code>
 * <output>
 * 01:23
 * (depending on the current time)
 * </output>
 *
 * <code title="strtotime string">
 * <f:format.date format="d.m.Y - H:i:s">+1 week 2 days 4 hours 2 seconds</f:format.date>
 * </code>
 * <output>
 * 13.12.1980 - 21:03:42
 * (depending on the current time, see http://www.php.net/manual/en/function.strtotime.php)
 * </output>
 *
 * <code title="output date from unix timestamp">
 * <f:format.date format="d.m.Y - H:i:s">@{someTimestamp}</f:format.date>
 * </code>
 * <output>
 * 13.12.1980 - 21:03:42
 * (depending on the current time. Don't forget the "@" in front of the timestamp see http://www.php.net/manual/en/function.strtotime.php)
 * </output>
 *
 * <code title="Inline notation">
 * {f:format.date(date: dateObject)}
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the value of {dateObject})
 * </output>
 *
 * <code title="Inline notation (2nd variant)">
 * {dateObject -> f:format.date()}
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the value of {dateObject})
 * </output>
 *
 * <code title="Inline notation, outputting date only, using current locale">
 * {dateObject -> f:format.date(localeFormatType: 'date', forceLocale: true)}
 * </code>
 * <output>
 * 13.12.1980
 * (depending on the value of {dateObject} and the current locale)
 * </output>
 *
 * <code title="Inline notation with specific locale used">
 * {dateObject -> f:format.date(forceLocale: 'de_DE')}
 * </code>
 * <output>
 * 13.12.1980 11:15:42
 * (depending on the value of {dateObject})
 * </output>
 *
 * @api
 */
class DateViewHelper extends AbstractLocaleAwareViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * @Flow\Inject
	 * @var DatetimeFormatter
	 */
	protected $datetimeFormatter;

	/**
	 * Render the supplied DateTime object as a formatted date.
	 *
	 * @param mixed $date either a \DateTime object or a string that is accepted by \DateTime constructor
	 * @param string $format Format String which is taken to format the Date/Time if none of the locale options are set.
	 * @param string $localeFormatType Whether to format (according to locale set in $forceLocale) date, time or datetime. Must be one of TYPO3\Flow\I18n\Cldr\Reader\DatesReader::FORMAT_TYPE_*'s constants.
	 * @param string $localeFormatLength Format length if locale set in $forceLocale. Must be one of TYPO3\Flow\I18n\Cldr\Reader\DatesReader::FORMAT_LENGTH_*'s constants.
	 * @param string $cldrFormat Format string in CLDR format (see http://cldr.unicode.org/translation/date-time)
	 * @throws ViewHelperException
	 * @return string Formatted date
	 * @api
	 */
	public function render($date = NULL, $format = 'Y-m-d', $localeFormatType = NULL, $localeFormatLength = NULL, $cldrFormat = NULL) {
		if ($date === NULL) {
			$date = $this->renderChildren();
			if ($date === NULL) {
				return '';
			}
		}
		if (!$date instanceof \DateTime) {
			try {
				$date = new \DateTime($date);
			} catch (\Exception $exception) {
				throw new ViewHelperException('"' . $date . '" could not be parsed by \DateTime constructor.', 1241722579, $exception);
			}
		}

		$useLocale = $this->getLocale();
		if ($useLocale !== NULL) {
			try {
				if ($cldrFormat !== NULL) {
					$output = $this->datetimeFormatter->formatDateTimeWithCustomPattern($date, $cldrFormat, $useLocale);
				} else {
					$output = $this->datetimeFormatter->format($date, $useLocale, array($localeFormatType, $localeFormatLength));
				}
			} catch(I18nException $exception) {
				throw new ViewHelperException(sprintf('An error occurred while trying to format the given date/time: "%s"', $exception->getMessage()), 1342610987, $exception);
			}
		} else {
			$output = $date->format($format);
		}

		return $output;
	}
}
