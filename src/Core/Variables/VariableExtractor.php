<?php
namespace TYPO3\Fluid\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class VariableExtractor
 *
 * Extracts variables from arrays/objects by use
 * of array accessing and basic getter methods.
 */
class VariableExtractor {

	/**
	 * Static interface for instanciating and extracting
	 * in a single operation. Delegates to getByPath.
	 *
	 * @param mixed $subject
	 * @param string $propertyPath
	 */
	public static function extract($subject, $propertyPath) {
		$extractor = new self();
		return $extractor->getByPath($subject, $propertyPath);
	}

	/**
	 * Extracts a variable by path, recursively, from the
	 * subject pass in argument. This implementation supports
	 * recursive variable references by using {} around sub-
	 * references, e.g. "array.{index}" will first get the
	 * "array" variable, then resolve the "index" variable
	 * before using the value of "index" as name of the property
	 * to return. So:
	 *
	 * $subject = array('foo' => array('bar' => 'baz'), 'key' => 'bar')
	 * $propertyPath = 'foo.{key}';
	 * $result = ...getByPath($subject, $propertyPath);
	 * // $result value is "baz", because $subject['foo'][$subject['key']] = 'baz';
	 *
	 * @param mixed $subject
	 * @param string $propertyPath
	 * @return mixed
	 */
	public function getByPath($subject, $propertyPath) {
		$original = $subject;
		$propertyPathSegments = explode('.', $propertyPath);
		foreach ($propertyPathSegments as $pathSegment) {
			$start = strpos($pathSegment, '{');
			$end = strrpos($pathSegment, '}');
			if ($start === 0 && $end === strlen($pathSegment) - 1) {
				$pathSegment = $this->extractSingleValue($original, substr($pathSegment, 1, -1));
			} elseif ($start !== FALSE && $end !== FALSE) {
				$subValue = $this->extractSingleValue($original, substr($pathSegment, $start + 1, $end - $start - 1));
				$pathSegment = substr($pathSegment, 0, $start) . $subValue . substr($pathSegment, $end + 1);
			}
			$subject = $this->extractSingleValue($subject, $pathSegment);

			if ($subject === NULL) {
				break;
			}
		}
		return $subject;
	}

	/**
	 * Extracts a single value from an array or object.
	 *
	 * @param mixed $subject
	 * @param string $propertyPath
	 */
	protected function extractSingleValue($subject, $propertyPath) {
		$value = is_object($subject) && isset($subject->$propertyPath) || is_array($subject) && isset($subject[$propertyPath])
			? (is_array($subject) || $subject instanceof \ArrayAccess ? $subject[$propertyPath] : $subject->$propertyPath)
			: NULL;
		return $value;
	}

}
