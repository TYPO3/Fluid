<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Cache;

use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Cache\StandardCacheWarmer;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;

class SimpleFileCacheTest extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function getCacheWarmerReturnsStandardCacheWarmer(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        self::assertInstanceOf(StandardCacheWarmer::class, $cache->getCacheWarmer());
    }

    /**
     * @test
     */
    public function getReturnsFalseWhenNotFound(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $result = $cache->get('test');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function getReturnsTrueWhenFound(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $result = $cache->get('DateTime');
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function addToCacheCreatesFile(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        self::assertFileExists(self::$cachePath . '/' . 'test.php');
    }

    /**
     * @test
     */
    public function getLoadsFile(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        $cache->get('test');
        self::assertTrue(class_exists('MyCachedClass', false));
    }

    /**
     * @test
     */
    public function flushAll(): void
    {
        $cache = $this->getMock(
            SimpleFileCache::class,
            ['getCachedFilenames', 'flushByFilename', 'flushByname'],
            [self::$cachePath]
        );
        $cache->expects(self::never())->method('flushByName');
        $cache->expects(self::once())->method('getCachedFilenames')->willReturn(['foo']);
        $cache->expects(self::once())->method('flushByFilename')->with('foo');
        $cache->flush();
    }

    /**
     * @test
     */
    public function flushByName(): void
    {
        $cache = $this->getMock(
            SimpleFileCache::class,
            ['getCachedFilenames', 'flushByFilename', 'flushByname'],
            [self::$cachePath]
        );
        $cache->expects(self::once())->method('flushByName')->with('foo');
        $cache->expects(self::never())->method('getCachedFilenames');
        $cache->expects(self::never())->method('flushByFilename');
        $cache->flush('foo');
    }

    /**
     * @test
     */
    public function flushByNameDeletesSingleFile(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        $cache->set('test2', '<?php' . PHP_EOL . 'class MyOtherCachedClass {}' . PHP_EOL);
        $cache->flush('test');
        self::assertFileExists(self::$cachePath . '/' . 'test2.php');
        if (method_exists($this, 'assertFileDoesNotExist')) {
            self::assertFileDoesNotExist(self::$cachePath . '/' . 'test.php');
        } else {
            // @todo: Remove fallback when phpunit >= 9 is required.
            self::assertFileNotExists(self::$cachePath . '/' . 'test.php');
        }
    }

    /**
     * @test
     */
    public function setThrowsRuntimeExceptionOnInvalidDirectory(): void
    {
        $this->expectException(\RuntimeException::class);
        $cache = new SimpleFileCache('/does/not/exist');
        $cache->set('foo', 'bar');
    }
}
