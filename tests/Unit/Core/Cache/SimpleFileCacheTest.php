<?php

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Cache\StandardCacheWarmer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class SimpleFileCacheTest
 */
class SimpleFileCacheTest extends UnitTestCase
{

    /**
     * @var vfsStreamDirectory
     */
    protected $directory;

    public function setUp(): void
    {
        $this->directory = vfsStream::setup('cache');
    }

    /**
     * @test
     */
    public function testGetCacheWarmerReturnsStandardCacheWarmer()
    {
        $cache = new SimpleFileCache(vfsStream::url('cache/'));
        self::assertInstanceOf(StandardCacheWarmer::class, $cache->getCacheWarmer());
    }

    /**
     * @test
     */
    public function testGetReturnsFalseWhenNotFound()
    {
        $cache = new SimpleFileCache(vfsStream::url('cache/'));
        $result = $cache->get('test');
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testGetReturnsTrueWhenFound()
    {
        $cache = new SimpleFileCache(vfsStream::url('cache/'));
        $result = $cache->get('DateTime');
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function testAddToCacheCreatesFile()
    {
        $cache = new SimpleFileCache(vfsStream::url('cache/'));
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        self::assertFileExists(vfsStream::url('cache/test.php'));
    }

    /**
     * @test
     */
    public function testGetLoadsFile()
    {
        $cache = new SimpleFileCache(vfsStream::url('cache/'));
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        $result = $cache->get('test');
        self::assertTrue(class_exists('MyCachedClass', false));
    }

    /**
     * @test
     */
    public function testFlushAll()
    {
        $cache = $this->getMock(
            SimpleFileCache::class,
            ['getCachedFilenames', 'flushByFilename', 'flushByname'],
            [vfsStream::url('cache/')]
        );
        $cache->expects(self::never())->method('flushByName');
        $cache->expects(self::once())->method('getCachedFilenames')->willReturn(['foo']);
        $cache->expects(self::once())->method('flushByFilename')->with('foo');
        $cache->flush();
    }

    /**
     * @test
     */
    public function testFlushByName()
    {
        $cache = $this->getMock(
            SimpleFileCache::class,
            ['getCachedFilenames', 'flushByFilename', 'flushByname'],
            [vfsStream::url('cache/')]
        );
        $cache->expects(self::once())->method('flushByName')->with('foo');
        $cache->expects(self::never())->method('getCachedFilenames');
        $cache->expects(self::never())->method('flushByFilename');
        $cache->flush('foo');
    }

    /**
     * @test
     */
    public function testFlushByNameDeletesSingleFile()
    {
        $cache = new SimpleFileCache(vfsStream::url('cache/'));
        $cache->set('test', '<?php' . PHP_EOL . 'class MyCachedClass {}' . PHP_EOL);
        $cache->set('test2', '<?php' . PHP_EOL . 'class MyOtherCachedClass {}' . PHP_EOL);
        $cache->flush('test');
        self::assertFileExists(vfsStream::url('cache/test2.php'));
        self::assertFileNotExists(vfsStream::url('cache/test.php'));
    }

    /**
     * @test
     */
    public function testSetThrowsRuntimeExceptionOnInvalidDirectory()
    {
        $cache = new SimpleFileCache('/does/not/exist');
        $this->setExpectedException('RuntimeException');
        $cache->set('foo', 'bar');
    }
}
