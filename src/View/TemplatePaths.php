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
    /**
     * @deprecated will be removed with Fluid v5
     */
    public const DEFAULT_TEMPLATES_DIRECTORY = 'Resources/Private/Templates/';
    /**
     * @deprecated will be removed with Fluid v5
     */
    public const DEFAULT_LAYOUTS_DIRECTORY = 'Resources/Private/Layouts/';
    /**
     * @deprecated will be removed with Fluid v5
     */
    public const DEFAULT_PARTIALS_DIRECTORY = 'Resources/Private/Partials/';
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

    /**
     * This constructor will be removed with Fluid v5 as the underlying methods are
     * deprecated and will be removed with Fluid v5 as well. The appropriate setters
     * (like setTemplateRootPaths()) should be called instead.
     */
    public function __construct(array|string|null $packageNameOrArray = null)
    {
        if (is_array($packageNameOrArray)) {
            $this->fillFromConfigurationArray($packageNameOrArray);
        } elseif (!empty($packageNameOrArray)) {
            $this->fillDefaultsByPackageName($packageNameOrArray);
        }
    }

    /**
     * @deprecated will be removed in Fluid v5; use the individual getters instead. Sanitation is not necessary
     *             because setters already sanitize input values
     */
    public function toArray(): array
    {
        trigger_error('toArray() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        return [
            self::CONFIG_TEMPLATEROOTPATHS => $this->sanitizePaths($this->getTemplateRootPaths()),
            self::CONFIG_LAYOUTROOTPATHS => $this->sanitizePaths($this->getLayoutRootPaths()),
            self::CONFIG_PARTIALROOTPATHS => $this->sanitizePaths($this->getPartialRootPaths()),
        ];
    }

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
        $format = $format ?: $this->getFormat();
        $controller = str_replace('\\', '/', $controller);
        $action = ucfirst($action);
        $identifier = ltrim($controller . '/' . $action . '.' . $format, '/');
        if (!array_key_exists($identifier, $this->resolvedFiles['templates'])) {
            $templateRootPaths = $this->getTemplateRootPaths();
            foreach ([$controller . '/' . $action, $action] as $possibleRelativePath) {
                $possibleRelativePath = ltrim($possibleRelativePath, '/');
                try {
                    return $this->resolvedFiles['templates'][$identifier] = $this->resolveFileInPaths($templateRootPaths, $possibleRelativePath, $format);
                } catch (InvalidTemplateResourceException $error) {
                    $this->resolvedFiles['templates'][$identifier] = null;
                }
            }
        }
        return isset($this->resolvedFiles[self::NAME_TEMPLATES][$identifier]) ? $this->resolvedFiles[self::NAME_TEMPLATES][$identifier] : null;
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
        return $this->resolveFilesInFolders($paths, $format ?: $this->getFormat());
    }

    /**
     * @return string[]
     */
    public function resolveAvailablePartialFiles(?string $format = null): array
    {
        return $this->resolveFilesInFolders($this->getPartialRootPaths(), $format ?: $this->getFormat());
    }

    /**
     * @return string[]
     */
    public function resolveAvailableLayoutFiles(?string $format = null): array
    {
        return $this->resolveFilesInFolders($this->getLayoutRootPaths(), $format ?: $this->getFormat());
    }

    /**
     * @param string[] $folders
     * @return string[]
     */
    protected function resolveFilesInFolders(array $folders, string $format): array
    {
        $files = [];
        foreach ($folders as $folder) {
            $files = array_merge($files, $this->resolveFilesInFolder($folder, $format));
        }
        return array_values($files);
    }

    /**
     * @return string[]
     */
    protected function resolveFilesInFolder(string $folder, string $format): array
    {
        if (!is_dir($folder)) {
            return [];
        }

        $directoryIterator = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::SELF_FIRST);
        $filterIterator = new \CallbackFilterIterator($recursiveIterator, function ($current, $key, $iterator) use ($format) {
            return $current->getExtension() === $format;
        });

        return array_keys(iterator_to_array($filterIterator));
    }

    /**
     * Fills path arrays based on a traditional
     * TypoScript array which may contain one or
     * more of the supported structures, in order
     * of priority:
     *
     * - `plugin.tx_yourext.view.templateRootPath` and siblings.
     * - `plugin.tx_yourext.view.templateRootPaths` and siblings.
     * - `plugin.tx_yourext.view.overlays.otherextension.templateRootPath` and siblings.
     *
     * The paths are treated as follows, using the
     * `template`-type paths as an example:
     *
     * - If `templateRootPath` is defined, it gets
     *   used as the _first_ path in the internal
     *   paths array.
     * - If `templateRootPaths` is defined, all
     *   values from it are _appended_ to the
     *   internal paths array.
     * - If `overlays.*` exists in the array it is
     *   iterated, each `templateRootPath` entry
     *   from it _appended_ to the internal array.
     *
     * The result is that after filling, the path
     * arrays will contain one or more entries in
     * the order described above, depending on how
     * many of the possible configurations were
     * present in the input array.
     *
     * Will replace any currently configured paths.
     *
     * @api
     * @deprecated will be removed with Fluid v5; appropriate setters (like setTemplateRootPaths())
     *             should be called instead
     */
    public function fillFromConfigurationArray(array $paths): void
    {
        trigger_error('fillFromConfigurationArray() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        list($templateRootPaths, $layoutRootPaths, $partialRootPaths, $format) = $this->extractPathArrays($paths);
        $this->setTemplateRootPaths($templateRootPaths);
        $this->setLayoutRootPaths($layoutRootPaths);
        $this->setPartialRootPaths($partialRootPaths);
        $this->setFormat($format);
    }

    /**
     * Fills path arrays with default expected paths
     * based on package name (converted to extension
     * key automatically).
     *
     * Will replace any currently configured paths.
     *
     * @api
     * @deprecated will be removed with Fluid v5; appropriate setters (like setTemplateRootPaths())
     *             should be called instead
     */
    public function fillDefaultsByPackageName(string $packageName): void
    {
        trigger_error('fillDefaultsByPackageName() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        $path = $this->getPackagePath($packageName);
        $this->setTemplateRootPaths([$path . self::DEFAULT_TEMPLATES_DIRECTORY]);
        $this->setLayoutRootPaths([$path . self::DEFAULT_LAYOUTS_DIRECTORY]);
        $this->setPartialRootPaths([$path . self::DEFAULT_PARTIALS_DIRECTORY]);
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

    /**
     * Guarantees that array $reference with paths
     * are turned into correct, absolute paths
     *
     * @param string[] $reference
     * @return string[]
     * @deprecated will be removed with Fluid v5
     */
    protected function ensureAbsolutePaths(array $reference): array
    {
        trigger_error('ensureAbsolutePaths() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        return array_map([$this, 'ensureAbsolutePath'], $reference);
    }

    protected function ensureSuffixedPath(string $path): string
    {
        return $path !== '' ? rtrim($path, '/') . '/' : '';
    }

    /**
     * Extract an array of three arrays of paths, one
     * for each of the types of Fluid file resources.
     * Accepts one or both of the singular and plural
     * path definitions in the input - returns the
     * combined collections of paths based on both
     * the singular and plural entries with the singular
     * entries being recorded first and plurals second.
     *
     * Adds legacy singular name as last option, if set.
     *
     * @deprecated will be removed with Fluid v5
     */
    protected function extractPathArrays(array $paths): array
    {
        trigger_error('extractPathArrays() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        $format = $this->getFormat();
        // pre-processing: if special parameters exist, extract them:
        if (isset($paths[self::CONFIG_FORMAT])) {
            $format = $paths[self::CONFIG_FORMAT];
        }
        $pathParts = [
            self::CONFIG_TEMPLATEROOTPATHS,
            self::CONFIG_LAYOUTROOTPATHS,
            self::CONFIG_PARTIALROOTPATHS,
        ];
        $pathCollections = [];
        foreach ($pathParts as $pathPart) {
            $partPaths = [];
            if (isset($paths[$pathPart]) && is_array($paths[$pathPart])) {
                $partPaths = array_merge($partPaths, $paths[$pathPart]);
            }
            $pathCollections[] = array_unique(array_map([$this, 'ensureSuffixedPath'], $partPaths));
        }
        $pathCollections = array_map([$this, 'ensureAbsolutePaths'], $pathCollections);
        $pathCollections[] = $format;
        return $pathCollections;
    }

    /**
     * @deprecated will be removed with Fluid v5
     */
    protected function getPackagePath(string $packageName): string
    {
        trigger_error('getPackagePath() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        return '';
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
        $layoutName = str_replace('.', '_', $layoutName);
        $prefix = 'layout_' . $layoutName . '_' . $this->getFormat();
        return $this->createIdentifierForFile($filePathAndFilename, $prefix);
    }

    /**
     * Resolve the path and file name of the layout file, based on
     * $this->layoutPathAndFilename and $this->layoutPathAndFilenamePattern.
     *
     * In case a layout has already been set with setLayoutPathAndFilename(),
     * this method returns that path, otherwise a path and filename will be
     * resolved using the layoutPathAndFilenamePattern.
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
        $prefix = ltrim($controller . '_action_' . $action, '_');
        return $this->createIdentifierForFile($templatePathAndFilename, $prefix);
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
                    'Tried resolving a template file for controller action "%s->%s" in format ".%s", but none of the paths ' .
                    'contained the expected template file (%s). %s',
                    $controller,
                    $action,
                    $format,
                    $templateReference === null ? $controller . '/' . ucfirst($action) . '.' . $format : $templateReference,
                    count($this->getTemplateRootPaths()) ? 'The following paths were checked: ' . implode(', ', $this->getTemplateRootPaths()) : 'No paths configured.',
                ),
                1257246929,
            );
        }
        return file_get_contents($templateReference);
    }

    /**
     * Returns a unique identifier for the given file in the format
     * <PackageKey>_<SubPackageKey>_<ControllerName>_<prefix>_<hash>
     * The SH1 hash is a checksum that is based on the file path and last modification date
     */
    protected function createIdentifierForFile(?string $pathAndFilename, string $prefix): string
    {
        $templateModifiedTimestamp = $pathAndFilename !== null && $pathAndFilename !== 'php://stdin' && file_exists($pathAndFilename) ? filemtime($pathAndFilename) : 0;
        return sprintf('%s_%s', $prefix, hash('xxh3', $pathAndFilename . '|' . $templateModifiedTimestamp));
    }

    /**
     * Resolve the path and file name of the layout file, based on
     * $this->options['layoutPathAndFilename'] and $this->options['layoutPathAndFilenamePattern'].
     *
     * In case a layout has already been set with setLayoutPathAndFilename(),
     * this method returns that path, otherwise a path and filename will be
     * resolved using the layoutPathAndFilenamePattern.
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
        $layoutName = ucfirst($layoutName);
        $layoutKey = $layoutName . '.' . $this->getFormat();
        if (!array_key_exists($layoutKey, $this->resolvedFiles[self::NAME_LAYOUTS])) {
            $paths = $this->getLayoutRootPaths();
            $this->resolvedFiles[self::NAME_LAYOUTS][$layoutKey] = $this->resolveFileInPaths($paths, $layoutName);
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
            $prefix = 'partial_' . $partialName;
            $this->resolvedIdentifiers[self::NAME_PARTIALS][$partialKey] = $this->createIdentifierForFile($partialPathAndFilename, $prefix);
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
     * Resolve the partial path and filename based on $this->options['partialPathAndFilenamePattern'].
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
            $partialName = ucfirst($partialName);
            $this->resolvedFiles[self::NAME_PARTIALS][$partialKey] = $this->resolveFileInPaths($paths, $partialName);
        }
        return $this->resolvedFiles[self::NAME_PARTIALS][$partialKey];
    }

    /**
     * @param string[] $paths
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    protected function resolveFileInPaths(array $paths, string $relativePathAndFilename, ?string $format = null): string
    {
        $format = $format ?: $this->getFormat();
        $tried = [];
        // Note about loop: iteration with while + array_pop causes paths to be checked in opposite
        // order, which is intentional. Paths are considered overlays, e.g. adding a path to the
        // array means you want that path checked first.
        while (null !== ($path = array_pop($paths))) {
            $pathAndFilenameWithoutFormat = $path . $relativePathAndFilename;
            $pathAndFilename = $pathAndFilenameWithoutFormat . '.' . $format;
            if (is_file($pathAndFilename)) {
                return $pathAndFilename;
            }
            $tried[] = $pathAndFilename;
            if (is_file($pathAndFilenameWithoutFormat)) {
                return $pathAndFilenameWithoutFormat;
            }
            $tried[] = $pathAndFilenameWithoutFormat;
        }
        throw new InvalidTemplateResourceException(
            'The Fluid template files "' . implode('", "', $tried) . '" could not be loaded.',
            1225709595,
        );
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
