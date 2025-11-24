<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\View;

use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Template Paths Holder
 *
 * Class used to hold and resolve template files
 * and paths in multiple supported ways.
 *
 * The purpose of this class is to homogenise the
 * API that is used when working with template
 * paths coming from TypoScript, as well as serve
 * as a way to quickly generate default template-,
 * layout- and partial root paths by package.
 *
 * The constructor accepts two different types of
 * input - anything not of those types is silently
 * ignored:
 *
 * - a `string` input is assumed a package name
 *   and will call the `fillDefaultsByPackageName`
 *   value filling method.
 * - an `array` input is assumed a TypoScript-style
 *   array of root paths in one or more of the
 *   supported structures and will call the
 *   `fillFromTypoScriptArray` method.
 *
 * Either method can also be called after instance
 * is created, but both will overwrite any paths
 * you have previously configured.
 */
class TemplatePaths
{
    public const FLUID_EXTENSION = 'fluid';
    public const DEFAULT_FORMAT = 'html';
    public const CONFIG_TEMPLATEROOTPATHS = 'templateRootPaths';
    public const CONFIG_LAYOUTROOTPATHS = 'layoutRootPaths';
    public const CONFIG_PARTIALROOTPATHS = 'partialRootPaths';
    public const CONFIG_FORMAT = 'format';
    public const NAME_TEMPLATES = 'templates';
    public const NAME_LAYOUTS = 'layouts';
    public const NAME_PARTIALS = 'partials';

    /**
     * Holds already resolved identifiers for template files
     *
     * @var array<string, string[]>
     */
    protected array $resolvedIdentifiers = [
        self::NAME_TEMPLATES => [],
        self::NAME_LAYOUTS => [],
        self::NAME_PARTIALS => [],
    ];

    /**
     * Holds already resolved identifiers for template files
     *
     * @var array<string, string[]>
     */
    protected array $resolvedFiles = [
        self::NAME_TEMPLATES => [],
        self::NAME_LAYOUTS => [],
        self::NAME_PARTIALS => [],
    ];

    /**
     * @var string[]
     */
    protected array $templateRootPaths = [];

    /**
     * @var string[]
     */
    protected array $layoutRootPaths = [];

    /**
     * @var string[]
     */
    protected array $partialRootPaths = [];

    protected ?string $templatePathAndFilename = null;

    protected ?string $layoutPathAndFilename = null;

    /**
     * @todo this can also contain resources, see getTemplateSource(). We should check if this is necessary.
     *
     * @var string|resource|null
     */
    protected mixed $templateSource = null;

    protected string $format = self::DEFAULT_FORMAT;

    public function setTemplatePathAndFilename(string $templatePathAndFilename): void
    {
        $this->templatePathAndFilename = $this->sanitizePath($templatePathAndFilename);
    }

    public function setLayoutPathAndFilename(string $layoutPathAndFilename): void
    {
        $this->layoutPathAndFilename = $this->sanitizePath($layoutPathAndFilename);
    }

    /**
     * @return string[]
     */
    public function getTemplateRootPaths(): array
    {
        return $this->templateRootPaths;
    }

    /**
     * @param string[] $templateRootPaths
     */
    public function setTemplateRootPaths(array $templateRootPaths): void
    {
        $this->templateRootPaths = $this->sanitizePaths($templateRootPaths);
        $this->clearResolvedIdentifiersAndTemplates(self::NAME_TEMPLATES);
    }

    public function getLayoutRootPaths(): array
    {
        return $this->layoutRootPaths;
    }

    /**
     * @param string[] $layoutRootPaths
     */
    public function setLayoutRootPaths(array $layoutRootPaths): void
    {
        $this->layoutRootPaths = $this->sanitizePaths($layoutRootPaths);
        $this->clearResolvedIdentifiersAndTemplates(self::NAME_LAYOUTS);
    }

    /**
     * @return string[]
     */
    public function getPartialRootPaths(): array
    {
        return $this->partialRootPaths;
    }

    /**
     * @param string[] $partialRootPaths
     */
    public function setPartialRootPaths(array $partialRootPaths): void
    {
        $this->partialRootPaths = $this->sanitizePaths($partialRootPaths);
        $this->clearResolvedIdentifiersAndTemplates(self::NAME_PARTIALS);
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Attempts to resolve an absolute filename
     * of a template (i.e. `templateRootPaths`)
     * using a controller name, action and format.
     *
     * Works _backwards_ through template paths in
     * order to achieve an "overlay"-type behavior
     * where the last paths added are the first to
     * be checked and the first path added acts as
     * fallback if no other paths have the file.
     *
     * If the file does not exist in any path,
     * including fallback path, `null` is returned.
     *
     * Path configurations filled from TypoScript
     * is automatically recorded in the right
     * order (see `fillFromTypoScriptArray`), but
     * when manually setting the paths that should
     * be checked, you as user must be aware of
     * this reverse behavior (which you should
     * already be, given that it is the same way
     * TypoScript path configurations work).
     * @api
     */
    public function resolveTemplateFileForControllerAndActionAndFormat(string $controller, string $action, ?string $format = null): ?string
    {
        if ($this->templatePathAndFilename !== null) {
            return $this->templatePathAndFilename;
        }

        // Generate runtime cache identifier to check if template has already been resolved
        $format = $format ?: $this->getFormat();
        $controller = trim(str_replace('\\', '/', $controller), '/');
        $identifier = $controller . '/' . $action . '.' . $format;
        if (array_key_exists($identifier, $this->resolvedFiles[self::NAME_TEMPLATES])) {
            return $this->resolvedFiles[self::NAME_TEMPLATES][$identifier];
        }

        // Use controller name as path suffix if specified and resolve template
        if ($controller !== '') {
            $controllerTemplatePaths = array_map(
                fn(string $path): string => $path . $controller . '/',
                $this->getTemplateRootPaths(),
            );
            try {
                return $this->resolvedFiles[self::NAME_TEMPLATES][$identifier] = $this->resolveFileInPaths(
                    $controllerTemplatePaths,
                    $action,
                    $format,
                );
            } catch (InvalidTemplateResourceException $error) {
            }
        }

        // Resolve template based on action name
        try {
            return $this->resolvedFiles[self::NAME_TEMPLATES][$identifier] = $this->resolveFileInPaths(
                $this->getTemplateRootPaths(),
                $action,
                $format,
            );
        } catch (InvalidTemplateResourceException $error) {
        }

        // No template found, still add to runtime cache
        return $this->resolvedFiles[self::NAME_TEMPLATES][$identifier] = null;
    }

    /**
     * @return string[]
     */
    public function resolveAvailableTemplateFiles(?string $controllerName, ?string $format = null): array
    {
        $paths = $this->getTemplateRootPaths();
        foreach ($paths as $index => $path) {
            $paths[$index] = rtrim($path . ($controllerName ?? ''), '/') . '/';
        }
        $scanner = new TemplateFinder();
        return $scanner->findTemplatesByFileExtension($paths, $format ?: $this->getFormat());
    }

    /**
     * @return string[]
     */
    public function resolveAvailablePartialFiles(?string $format = null): array
    {
        $scanner = new TemplateFinder();
        return $scanner->findTemplatesByFileExtension($this->getPartialRootPaths(), $format ?: $this->getFormat());
    }

    /**
     * @return string[]
     */
    public function resolveAvailableLayoutFiles(?string $format = null): array
    {
        $scanner = new TemplateFinder();
        return $scanner->findTemplatesByFileExtension($this->getLayoutRootPaths(), $format ?: $this->getFormat());
    }

    /**
     * Sanitize a path, ensuring it is absolute and
     * if a directory, suffixed by a trailing slash.
     *
     * @todo $path should really be string. Array handling should not be part of this method, and other types
     *       (such as bool) should really not be passed to this method in the first place. Further refactoring
     *       is necessary to guarantee this.
     * @param mixed $path
     * @return string|string[]
     */
    protected function sanitizePath(mixed $path): string|array
    {
        if (is_array($path)) {
            $paths = array_map([$this, 'sanitizePath'], $path);
            return array_unique($paths);
        }
        if (($wrapper = parse_url((string)$path, PHP_URL_SCHEME)) && in_array($wrapper, stream_get_wrappers())) {
            return $path;
        }
        if (!empty($path)) {
            $path = str_replace(['\\', '//'], '/', (string)$path);
            $path = $this->ensureAbsolutePath($path);
            if (is_dir($path)) {
                $path = $this->ensureSuffixedPath($path);
            }
        }
        return (string)$path;
    }

    /**
     * Sanitize paths passing each through sanitizePath().
     *
     * @param string[] $paths
     * @return string[]
     */
    protected function sanitizePaths(array $paths): array
    {
        return array_unique(array_map([$this, 'sanitizePath'], $paths));
    }

    /**
     * Guarantees that $reference is turned into a
     * correct, absolute path.
     */
    protected function ensureAbsolutePath(string $path): string
    {
        return (!empty($path) && $path[0] !== '/' && $path[1] !== ':') ? $this->sanitizePath(realpath($path)) : $path;
    }

    protected function ensureSuffixedPath(string $path): string
    {
        return $path !== '' ? rtrim($path, '/') . '/' : '';
    }

    /**
     * Returns a unique identifier for the resolved layout file.
     * This identifier is based on the template path and last modification date
     *
     * @param string $layoutName The name of the layout
     * @return string layout identifier
     */
    public function getLayoutIdentifier(string $layoutName = 'Default'): string
    {
        $filePathAndFilename = $this->getLayoutPathAndFilename($layoutName);
        return $this->createIdentifierForFile($filePathAndFilename);
    }

    /**
     * Resolve the path and file name of the layout file, based on
     * $this->layoutPathAndFilename.
     *
     * @param string $layoutName Name of the layout to use. If none given, use "Default"
     * @return string Path and filename of layout file
     * @throws InvalidTemplateResourceException
     */
    public function getLayoutSource(string $layoutName = 'Default'): string
    {
        $layoutPathAndFilename = $this->getLayoutPathAndFilename($layoutName);
        return file_get_contents($layoutPathAndFilename);
    }

    /**
     * Returns a unique identifier for the resolved template file
     * This identifier is based on the template path and last modification date
     *
     * @param string $controller
     * @param string $action Name of the action. If null, will be taken from request.
     * @return string template identifier
     */
    public function getTemplateIdentifier(?string $controller = 'Default', ?string $action = 'Default'): string
    {
        /** @todo this needs further refactoring to avoid this fallback */
        $controller ??= '';
        $action ??= '';

        if ($this->templateSource !== null) {
            return 'source_' . hash('xxh3', (string)$this->templateSource) . '_' . $controller . '_' . $action . '_' . $this->getFormat();
        }
        $templatePathAndFilename = $this->resolveTemplateFileForControllerAndActionAndFormat($controller, $action);
        return $this->createIdentifierForFile($templatePathAndFilename);
    }

    /**
     * @param mixed $source
     */
    public function setTemplateSource(mixed $source): void
    {
        $this->templateSource = $source;
    }

    /**
     * Resolve the template path and filename for the given action. If $actionName
     * is null, looks into the current request.
     *
     * @param string $controller
     * @param string $action Name of the action. If null, will be taken from request.
     * @return string Full path to template
     * @throws InvalidTemplateResourceException
     */
    public function getTemplateSource(?string $controller = 'Default', ?string $action = 'Default')
    {
        /** @todo this needs further refactoring to avoid this fallback */
        $controller ??= '';
        $action ??= '';

        if (is_string($this->templateSource)) {
            return $this->templateSource;
        }
        if (is_resource($this->templateSource)) {
            rewind($this->templateSource);
            return $this->templateSource = stream_get_contents($this->templateSource);
        }
        $templateReference = $this->resolveTemplateFileForControllerAndActionAndFormat($controller, $action);
        if (!file_exists((string)$templateReference) && $templateReference !== 'php://stdin') {
            $format = $this->getFormat();
            throw new InvalidTemplateResourceException(
                sprintf(
                    'Tried resolving a template file for controller action "%s->%s" in format ".%s", but none of the paths '
                    . 'contained the expected template file (%s). %s',
                    $controller,
                    $action,
                    $format,
                    $templateReference === null ? $controller . '/' . $action . '.' . $format : $templateReference,
                    count($this->getTemplateRootPaths()) ? 'The following paths were checked: ' . implode(', ', $this->getTemplateRootPaths()) : 'No paths configured.',
                ),
                1257246929,
            );
        }
        return file_get_contents($templateReference);
    }

    /**
     * Returns a unique identifier for the given file in the format
     * <FileName>_<hash>
     * The hash is a checksum that is based on the file path and last modification date
     */
    protected function createIdentifierForFile(?string $pathAndFilename): string
    {
        $templateModifiedTimestamp = 0;
        $prefix = '';
        if ($pathAndFilename !== null && $pathAndFilename !== 'php://stdin' && file_exists($pathAndFilename)) {
            $templateModifiedTimestamp = filemtime($pathAndFilename);
            $prefix = str_replace('.', '_', basename($pathAndFilename));
        }
        return sprintf('template_%s_%s', $prefix, hash('xxh3', $pathAndFilename . '|' . $templateModifiedTimestamp));
    }

    /**
     * Resolve the path and file name of the layout file, based on
     * $this->options['layoutPathAndFilename'].
     *
     * In case a layout has already been set with setLayoutPathAndFilename(),
     * this method returns that path, otherwise a path and filename will be
     * resolved.
     *
     * @param string $layoutName Name of the layout to use. If none given, use "Default"
     * @return string Path and filename of layout files
     * @throws Exception\InvalidTemplateResourceException
     */
    public function getLayoutPathAndFilename(string $layoutName = 'Default'): string
    {
        if ($this->layoutPathAndFilename !== null) {
            return $this->layoutPathAndFilename;
        }
        $layoutKey = $layoutName . '.' . $this->getFormat();
        if (!array_key_exists($layoutKey, $this->resolvedFiles[self::NAME_LAYOUTS])) {
            $paths = $this->getLayoutRootPaths();
            $this->resolvedFiles[self::NAME_LAYOUTS][$layoutKey] = $this->resolveFileInPaths($paths, $layoutName, $this->getFormat());
        }
        return $this->resolvedFiles[self::NAME_LAYOUTS][$layoutKey];
    }

    /**
     * Returns a unique identifier for the resolved partial file.
     * This identifier is based on the template path and last modification date
     *
     * @param string $partialName The name of the partial
     * @return string partial identifier
     */
    public function getPartialIdentifier(string $partialName): string
    {
        $partialKey = $partialName . '.' . $this->getFormat();
        if (!array_key_exists($partialKey, $this->resolvedIdentifiers[self::NAME_PARTIALS])) {
            $partialPathAndFilename = $this->getPartialPathAndFilename($partialName);
            $this->resolvedIdentifiers[self::NAME_PARTIALS][$partialKey] = $this->createIdentifierForFile($partialPathAndFilename);
        }
        return $this->resolvedIdentifiers[self::NAME_PARTIALS][$partialKey];
    }

    /**
     * Figures out which partial to use.
     *
     * @param string $partialName The name of the partial
     * @return string contents of the partial template
     * @throws InvalidTemplateResourceException
     */
    public function getPartialSource(string $partialName): string
    {
        $partialPathAndFilename = $this->getPartialPathAndFilename($partialName);
        return file_get_contents($partialPathAndFilename);
    }

    /**
     * Resolve the partial path and filename
     *
     * @param string $partialName The name of the partial
     * @return string the full path which should be used. The path definitely exists.
     * @throws InvalidTemplateResourceException
     */
    public function getPartialPathAndFilename(string $partialName): string
    {
        $partialKey = $partialName . '.' . $this->getFormat();
        if (!array_key_exists($partialKey, $this->resolvedFiles[self::NAME_PARTIALS])) {
            $paths = $this->getPartialRootPaths();
            $this->resolvedFiles[self::NAME_PARTIALS][$partialKey] = $this->resolveFileInPaths($paths, $partialName, $this->getFormat());
        }
        return $this->resolvedFiles[self::NAME_PARTIALS][$partialKey];
    }

    /**
     * Selects the template file that best matches the input template name from the available paths.
     *
     * @param string[] $paths
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    protected function resolveFileInPaths(array $paths, string $fileName, string $format): string
    {
        // Create array of possible template paths. This includes:
        // * with and without format as file extension
        // * with and without *.fluid.* file extension
        // * fallback to uppercase file name
        $possibleTemplates = [];
        foreach (array_reverse($paths) as $path) {
            $possibleTemplates[] = $path . $fileName . '.' . static::FLUID_EXTENSION . '.' . $format;
            $possibleTemplates[] = $path . $fileName . '.' . $format;
            $possibleTemplates[] = $path . $fileName;
            $uppercaseName = ucfirst($fileName);
            if ($uppercaseName !== $fileName) {
                $possibleTemplates[] = $path . $uppercaseName . '.' . static::FLUID_EXTENSION . '.' . $format;
                $possibleTemplates[] = $path . $uppercaseName . '.' . $format;
                $possibleTemplates[] = $path . $uppercaseName;
            }
        }

        foreach ($possibleTemplates as $templatePath) {
            if (is_file($templatePath)) {
                return $templatePath;
            }
        }

        throw new InvalidTemplateResourceException(sprintf(
            'The Fluid template file "%s" could not be loaded. Tried paths: "%s"',
            $fileName,
            implode('", "', $possibleTemplates),
        ), 1225709595);
    }

    protected function clearResolvedIdentifiersAndTemplates(?string $type = null): void
    {
        if ($type !== null) {
            $this->resolvedIdentifiers[$type] = $this->resolvedFiles[$type] = [];
        } else {
            $this->resolvedIdentifiers = $this->resolvedFiles = [
                self::NAME_TEMPLATES => [],
                self::NAME_LAYOUTS => [],
                self::NAME_PARTIALS => [],
            ];
        }
    }
}
