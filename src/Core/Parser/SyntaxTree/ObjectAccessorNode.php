<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which handles object access. This means it handles structures like {object.accessor.bla}
 */
class ObjectAccessorNode extends AbstractNode {

	/**
	 * Object path which will be called. Is a list like "post.name.email"
	 *
	 * @var string
	 */
	protected $objectPath;

	/**
	 * @var array
	 */
	protected static $variables = array();

	/**
	 * Constructor. Takes an object path as input.
	 *
	 * The first part of the object path has to be a variable in the
	 * TemplateVariableContainer.
	 *
	 * @param string $objectPath An Object Path, like object1.object2.object3
	 */
	public function __construct($objectPath) {
		$this->objectPath = $objectPath;
	}


	/**
	 * Internally used for building up cached templates; do not use directly!
	 *
	 * @return string
	 */
	public function getObjectPath() {
		return $this->objectPath;
	}

	/**
	 * Evaluate this node and return the correct object.
	 *
	 * Handles each part (denoted by .) in $this->objectPath in the following order:
	 * - call appropriate getter
	 * - call public property, if exists
	 * - fail
	 *
	 * The first part of the object path has to be a variable in the
	 * TemplateVariableContainer.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return object The evaluated object, can be any object type.
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		self::$variables = $renderingContext->getTemplateVariableContainer()->getAll();
		switch (strtolower($this->objectPath)) {
			case '_all':
				return self::$variables;

			case 'true':
			case 'on':
			case 'yes':
				return TRUE;

			case 'false':
			case 'off':
			case 'no':
				return FALSE;
			default:
				return self::getPropertyPath(self::$variables, $this->objectPath, $renderingContext);
		}
	}

	/**
	 * Gets a property path from a given object or array.
	 *
	 * If propertyPath is "bla.blubb", then we first call getProperty($object, 'bla'),
	 * and on the resulting object we call getProperty(..., 'blubb').
	 *
	 * For arrays the keys are checked likewise.
	 *
	 * @param mixed $subject An object or array
	 * @param string $propertyPath
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed Value of the property
	 */
	static public function getPropertyPath($subject, $propertyPath, RenderingContextInterface $renderingContext) {
		$propertyPathSegments = explode('.', $propertyPath);
		foreach ($propertyPathSegments as $pathSegment) {
			$start = strpos($pathSegment, '{');
			$end = strrpos($pathSegment, '}');
			if ($start === 0 && $end === strlen($pathSegment) - 1) {
				$pathSegment = self::getPropertyPath(self::$variables, substr($pathSegment, 1, -1), $renderingContext);
			} elseif ($start !== FALSE && $end !== FALSE) {
				$subValue = self::getPropertyPath(self::$variables, substr($pathSegment, $start + 1, $end - $start - 1), $renderingContext);
				$pathSegment = substr($pathSegment, 0, $start) . $subValue . substr($pathSegment, $end + 1);
			}
			$subject = is_object($subject) && isset($subject->$pathSegment) || is_array($subject) && isset($subject[$pathSegment])
				? (is_array($subject) || $subject instanceof \ArrayAccess ? $subject[$pathSegment] : $subject->$pathSegment)
				: NULL;

			if ($subject === NULL) {
				break;
			}
		}
		return $subject;
	}
}
