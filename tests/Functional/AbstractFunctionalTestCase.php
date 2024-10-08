<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional;

use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;

/**
 * Base test case for functional tests.
 *
 * This class provides some helpers for functional tests. It especially sets up a cache directory
 * for single test cases to be used functional tests to verify if template rendering works well when
 * called a second (then cached) time.
 * Tests should then basically instantiate a second view for the some template and call render().
 * Instantiating a new view to test caching behavior is important to avoid internal view and parsing
 * state related local cache properties. See the existing implementations for proper examples.
 *
 * @internal Framework internal for now to see how it evolves over time. This class is currently *not*
 *           exported as package file. Extensions with own view helpers can not rely on it as test base.
 *           This may change in case this abstract stabilizes, later.
 */
abstract class AbstractFunctionalTestCase extends TestCase
{
    protected static FluidCacheInterface $cache;

    /**
     * @var string Absolute path to cache directory
     */
    protected static string $cachePath;

    public function setUp(): void
    {
        self::$cachePath = sys_get_temp_dir() . '/' . 'fluid-functional-tests-' . hash('xxh3', __CLASS__);
        mkdir(self::$cachePath);
        self::$cache = (new SimpleFileCache(self::$cachePath));
    }

    public function tearDown(): void
    {
        self::$cache->flush();
        rmdir(self::$cachePath);
    }
}
