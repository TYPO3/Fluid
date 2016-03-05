<?php
namespace TYPO3Fluid\Fluid\Core\Variables;

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

	const ACCESSOR_ARRAY = 'array';
	const ACCESSOR_GETTER = 'getter';
	const ACCESSOR_ASSERTER = 'asserter';
	const ACCESSOR_PUBLICPROPERTY = 'public';

	/**
	 * Static interface for instanciating and extracting
	 * in a single operation. Delegates to getByPath.
	 *
	 * @param mixed $subject
	 * @param string $propertyPath
	 * @param array $accessors
	 * @return mixed
	 */
	public static function extract($subject, $propertyPath, array $accessors = array()) {
		$extractor = new self();
		return $extractor->getByPath($subject, $propertyPath, $accessors);
	}

	/**
	 * Static interface for instanciating and extracting
	 * accessors for each segment of the path.
	 *
	 * @param VariableProviderInterface $subject
	 * @param string $propertyPath
	 * @return mixed
	 */
	public static function extractAccessors($subject, $propertyPath) {
		$extractor = new self();
		return $extractor->getAccessorsForPath($subject, $propertyPath);
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
	 * @param array $accessors
	 * @return mixed
	 */
	public function getByPath($subject, $propertyPath, array $accessors = array()) {
		if ($subject instanceof StandardVariableProvider) {
			return $subject->getByPath($propertyPath, $accessors);
		}

		$propertyPathSegments = explode('.', $propertyPath);
		$propertyPathSegments = $this->resolveSubVariableReferences($subject, $propertyPathSegments);
		foreach ($propertyPathSegments as $index => $pathSegment) {
			$accessor = isset($accessors[$index]) ? $accessors[$index] : NULL;
			$subject = $this->extractSingleValue($subject, $pathSegment, $accessor);
			if ($subject === NULL) {
				break;
			}
		}
		return $subject;
	}

	/**
	 * @param mixed $subject
	 * @param string $propertyPath
	 * @return array
	 */
	public function getAccessorsForPath($subject, $propertyPath) {
		$accessors = array();
		$propertyPathSegments = explode('.', $propertyPath);
		foreach ($propertyPathSegments as $index => $pathSegment) {
			$accessor = $this->detectAccessor($subject, $pathSegment);
			if ($accessor === NULL) {
				// Note: this may include cases of sub-variable references. When such
				// a reference is encountered the accessor chain is stopped and new
				// accessors will be detected for the sub-variable and all following
				// path segments since the variable is now fully dynamic.
				break;
			}
			$accessors[] = $accessor;
			$subject = $this->extractSingleValue($subject, $pathSegment);
		}
		return $accessors;
	}

	/**
	 * @param mixed $subject
	 * @param array $segments
	 * @return array
	 */
	protected function resolveSubVariableReferences($subject, array $segments) {
		foreach ($segments as $index => $pathSegment) {
			$start = strpos($pathSegment, '{');
			$end = strrpos($pathSegment, '}');
			if ($start === 0 && $end === strlen($pathSegment) - 1) {
				$pathSegment = $this->extractSingleValue($subject, substr($pathSegment, 1, -1));
			} elseif ($start !== FALSE && $end !== FALSE) {
				$subValue = $this->extractSingleValue($subject, substr($pathSegment, $start + 1, $end - $start - 1));
				$pathSegment = substr($pathSegment, 0, $start) . $subValue . substr($pathSegment, $end + 1);
			}
			$segments[$index] = $pathSegment;
		}
		return $segments;
	}

	/**
	 * Extracts a single value from an array or object.
	 *
	 * @param mixed $subject
	 * @param string $propertyName
	 * @param string|null $accessor
	 * @return mixed
	 */
	protected function extractSingleValue($subject, $propertyName, $accessor = NULL) {
		if (!$accessor || !$this->canExtractWithAccessor($subject, $propertyName, $accessor)) {
			$accessor = $this->detectAccessor($subject, $propertyName);
		}
		return $this->extractWithAccessor($subject, $propertyName, $accessor);
	}

	/**
	 * Returns TRUE if the data type of $subject is potentially compatible
	 * with the $accessor.
	 *
	 * @param mixed $subject
	 * @param string $propertyName
	 * @param string $accessor
	 * @return boolean
	 */
	protected function canExtractWithAccessor($subject, $propertyName, $accessor) {
		$class = is_object($subject) ? get_class($subject) : FALSE;
		if ($accessor === self::ACCESSOR_ARRAY) {
			return (is_array($subject) || $subject instanceof \ArrayAccess);
		} elseif ($accessor === self::ACCESSOR_GETTER) {
			return ($class !== FALSE && method_exists($subject, 'get' . ucfirst($propertyName)));
		} elseif ($accessor === self::ACCESSOR_ASSERTER) {
			return ($class !== FALSE && method_exists($subject, 'is' . ucfirst($propertyName)));
		} elseif ($accessor === self::ACCESSOR_PUBLICPROPERTY) {
			return ($class !== FALSE && property_exists($subject, $propertyName));
		}
		return FALSE;
	}

	/**
	 * @param mixed $subject
	 * @param string $propertyName
	 * @param string $accessor
	 * @return mixed
	 */
	protected function extractWithAccessor($subject, $propertyName, $accessor) {
		if ($accessor === self::ACCESSOR_ARRAY && is_array($subject) && array_key_exists($propertyName, $subject)
			|| $subject instanceof \ArrayAccess && $subject->offsetExists($propertyName)
		) {
			return $subject[$propertyName];
		} elseif (is_object($subject)) {
			if ($accessor === self::ACCESSOR_GETTER) {
				return call_user_func_array(array($subject, 'get' . ucfirst($propertyName)), array());
			} elseif ($accessor === self::ACCESSOR_ASSERTER) {
				return call_user_func_array(array($subject, 'is' . ucfirst($propertyName)), array());
			} elseif ($accessor === self::ACCESSOR_PUBLICPROPERTY && property_exists($subject, $propertyName)) {
				return $subject->$propertyName;
			}
		}
		return NULL;
	}

	/**
	 * Detect which type of accessor to use when extracting
	 * $propertyName from $subject.
	 *
	 * @param mixed $subject
	 * @param string $propertyName
	 * @return string|NULL
	 */
	protected function detectAccessor($subject, $propertyName) {
		if (is_array($subject) || $subject instanceof \ArrayAccess) {
			return self::ACCESSOR_ARRAY;
		} elseif (is_object($subject)) {
			$upperCasePropertyName = ucfirst($propertyName);
			$getter = 'get' . $upperCasePropertyName;
			$asserter = 'is' . $upperCasePropertyName;
			if (method_exists($subject, $getter)) {
				return self::ACCESSOR_GETTER;
			}
			if (method_exists($subject, $asserter)) {
				return self::ACCESSOR_ASSERTER;
			}
			if (property_exists($subject, $propertyName)) {
				return self::ACCESSOR_PUBLICPROPERTY;
			}
			return NULL;
		}
		return NULL;
	}

}
