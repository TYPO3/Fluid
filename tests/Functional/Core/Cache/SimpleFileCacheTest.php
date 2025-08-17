<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Cache;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;

final class SimpleFileCacheTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function getReturnsFalseWhenNotFound(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $result = $cache->get('test');
        self::assertFalse($result);
    }

    #[Test]
    public function getReturnsTrueWhenFound(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $result = $cache->get('DateTime');
        self::assertTrue($result);
    }

    #[Test]
    public function addToCacheCreatesFile(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        self::assertFileExists(self::$cachePath . '/' . 'test.php');
    }

    #[Test]
    public function getLoadsFile(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        $cache->get('test');
        self::assertTrue(class_exists('MyCachedClass', false));
    }

    #[Test]
    public function flushWithoutNameCallsGetCachedFilenamesAndFlushByFilename(): void
    {
        $cache = $this->getMockBuilder(SimpleFileCache::class)
            ->onlyMethods(['getCachedFilenames', 'flushByFilename', 'flushByName'])
            ->getMock();
        $cache->expects(self::never())->method('flushByName');
        $cache->expects(self::once())->method('getCachedFilenames')->willReturn(['foo']);
        $cache->expects(self::once())->method('flushByFilename')->with('foo');
        $cache->flush();
    }

    #[Test]
    public function flushWithNameCallsFlushByName(): void
    {
        $cache = $this->getMockBuilder(SimpleFileCache::class)
            ->onlyMethods(['getCachedFilenames', 'flushByFilename', 'flushByName'])
            ->getMock();
        $cache->expects(self::once())->method('flushByName')->with('foo');
        $cache->expects(self::never())->method('getCachedFilenames');
        $cache->expects(self::never())->method('flushByFilename');
        $cache->flush('foo');
    }

    #[Test]
    public function flushByNameDeletesSingleFile(): void
    {
        $cache = new SimpleFileCache(self::$cachePath);
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        $cache->set('test2', '<?php' . PHP_EOL . 'class MyOtherCachedClass {}' . PHP_EOL);
        $cache->flush('test');
        self::assertFileExists(self::$cachePath . '/' . 'test2.php');
        self::assertFileDoesNotExist(self::$cachePath . '/' . 'test.php');
    }

    #[Test]
    public function setThrowsRuntimeExceptionOnInvalidDirectory(): void
    {
        $this->expectException(\RuntimeException::class);
        $cache = new SimpleFileCache('/does/not/exist');
        $cache->set('foo', 'bar');
    }
}
