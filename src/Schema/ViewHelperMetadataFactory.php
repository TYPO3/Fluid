<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Schema;

use TYPO3Fluid\Fluid\Core\Parser\Patterns;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @internal
 */
final class ViewHelperMetadataFactory
{
    private const NAMESPACE_NAME_DIVIDER = 'ViewHelpers';
    private const CLASS_SUFFIX = 'ViewHelper';

    public function createFromViewhelperClass(string $className): ViewHelperMetadata
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('The specified ViewHelper class does not exist: ' . $className);
        }

        if (!is_subclass_of($className, ViewHelperInterface::class)) {
            throw new \InvalidArgumentException('The specified ViewHelper class does not implement the required interface: ' . $className);
        }

        if (!str_ends_with($className, self::CLASS_SUFFIX)) {
            throw new \InvalidArgumentException('ViewHelper class name must end with "ViewHelper": ' . $className);
        }

        if (!str_contains($className, '\\' . self::NAMESPACE_NAME_DIVIDER . '\\')) {
            throw new \InvalidArgumentException('ViewHelper namespace could not be determined automatically, "ViewHelpers" directory missing in path: ' . $className);
        }

        if ((new \ReflectionClass($className))->isAbstract()) {
            throw new \InvalidArgumentException('Metadata cannot be fetched from abstract ViewHelpers: ' . $className);
        }

        $docComment = (new \ReflectionClass($className))->getDocComment();
        if ($docComment === false) {
            $documentation = '';
            $docTags = [];
        } else {
            $docComment = $this->extractTextFromDocComment((string)$docComment);

            // Parse phpdoc tags
            $parts = preg_split('#^(@[a-z-]+)#m', $docComment, -1, PREG_SPLIT_DELIM_CAPTURE);
            $documentation = trim(array_shift($parts));

            // Collect phpdoc tags
            $docTags = [];
            $currentTag = null;
            foreach ($parts as $part) {
                if (str_starts_with($part, '@')) {
                    $docTags[$part] = '';
                    $currentTag = $part;
                } else {
                    $docTags[$currentTag] .= $part;
                }
            }
            $docTags = array_map(trim(...), $docTags);
        }

        [$namespace, $name] = $this->splitClassName($className);

        return new ViewHelperMetadata(
            className: $className,
            namespace: $namespace,
            name: $name,
            tagName: $this->generateTagName($name),
            documentation: $documentation,
            xmlNamespace: $this->generateXmlNamespace($namespace),
            docTags: $docTags,
            argumentDefinitions: (new \ReflectionClass($className))->newInstanceWithoutConstructor()->prepareArguments(),
            allowsArbitraryArguments: is_subclass_of($className, AbstractTagBasedViewHelper::class),
        );
    }

    private function extractTextFromDocComment(string $docComment): string
    {
        // Remove opening comment tag
        $docComment = preg_replace('#^/\*\*#', '', $docComment);

        // Remove closing comment tag
        $docComment = preg_replace('#\*/$#', '', $docComment);

        // Remove * and leading whitespace in each line
        $docComment = preg_replace('#^\s*\* ?#m', '', $docComment);

        return $docComment;
    }

    private function generateXmlNamespace(string $phpNamespace): string
    {
        return Patterns::NAMESPACEPREFIX . str_replace('\\', '/', $phpNamespace);
    }

    private function generateTagName(string $viewHelperName): string
    {
        $withoutSuffix = substr($viewHelperName, 0, -strlen(self::CLASS_SUFFIX));
        return implode('.', array_map(lcfirst(...), explode('\\', $withoutSuffix)));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitClassName(string $className): array
    {
        $splitPos = strrpos($className, '\\' . self::NAMESPACE_NAME_DIVIDER . '\\') + strlen('\\' . self::NAMESPACE_NAME_DIVIDER);
        $namespace = substr($className, 0, $splitPos);
        $name = substr($className, $splitPos + 1);
        return [$namespace, $name];
    }
}
