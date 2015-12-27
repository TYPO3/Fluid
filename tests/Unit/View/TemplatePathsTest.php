<?php
namespace FluidTYPO3Fluid\Flux\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Tests\BaseTestCase;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Class TemplatePathsTest
 */
class TemplatePathsTest extends BaseTestCase {

	/**
	 * @test
	 */
	public function setsLayoutPathAndFilename() {
		$instance = $this->getMock(TemplatePaths::class, array('sanitizePath'));
		$instance->expects($this->any())->method('sanitizePath')->willReturnArgument(0);
		$instance->setLayoutPathAndFilename('foobar');
		$this->assertAttributeEquals('foobar', 'layoutPathAndFilename', $instance);
	}

	/**
	 * @test
	 */
	public function setsTemplatePathAndFilename() {
		$instance = $this->getMock(TemplatePaths::class, array('sanitizePath'));
		$instance->expects($this->any())->method('sanitizePath')->willReturnArgument(0);
		$instance->setTemplatePathAndFilename('foobar');
		$this->assertAttributeEquals('foobar', 'templatePathAndFilename', $instance);
	}

	/**
	 * @dataProvider getGetterAndSetterTestValues
	 * @param string $property
	 * @param mixed $value
	 */
	public function testGetterAndSetter($property, $value) {
		$getter = 'get' . ucfirst($property);
		$setter = 'set' . ucfirst($property);
		$instance = $this->getMock(TemplatePaths::class, array('sanitizePath'));
		$instance->expects($this->any())->method('sanitizePath')->willReturnArgument(0);
		$instance->$setter($value);
		$this->assertEquals($value, $instance->$getter());
	}

	/**
	 * @return array
	 */
	public function getGetterAndSetterTestValues() {
		return array(
			array('layoutRootPaths', array('foo' => 'bar')),
			array('templateRootPaths', array('foo' => 'bar')),
			array('partialRootPaths', array('foo' => 'bar'))
		);
	}

	/**
	 * @return void
	 */
	public function testFillByPackageName() {
		$instance = new TemplatePaths('FluidTYPO3.Flux');
		$this->assertNotEmpty($instance->getTemplateRootPaths());
	}

	/**
	 * @return void
	 */
	public function testFillByConfigurationArray() {
		$instance = new TemplatePaths(array(
			TemplatePaths::CONFIG_TEMPLATEROOTPATHS => array('Resources/Private/Templates/'),
			TemplatePaths::CONFIG_LAYOUTROOTPATHS => array('Resources/Private/Layouts/'),
			TemplatePaths::CONFIG_PARTIALROOTPATHS => array('Resources/Private/Partials/'),
			TemplatePaths::CONFIG_FORMAT => 'xml'
		));
		$this->assertNotEmpty($instance->getTemplateRootPaths());
	}

	/**
	 * @dataProvider getResolveFilesMethodTestValues
	 * @param string $method
	 */
	public function testResolveFilesMethodCallsResolveFilesInFolders($method, $pathsMethod) {
		$instance = $this->getMock(TemplatePaths::class, array('resolveFilesInFolders'));
		$instance->$pathsMethod(array('foo'));
		$instance->expects($this->once())->method('resolveFilesInFolders')->with($this->anything(), 'format');
		$instance->$method('format', 'format');
	}

	/**
	 * @return array
	 */
	public function getResolveFilesMethodTestValues() {
		return array(
			array('resolveAvailableTemplateFiles', 'setTemplateRootPaths'),
			array('resolveAvailablePartialFiles', 'setPartialRootPaths'),
			array('resolveAvailableLayoutFiles', 'setLayoutRootPaths')
		);
	}

	/**
	 * @return void
	 */
	public function testToArray() {
		$instance = $this->getMock(TemplatePaths::class, array('sanitizePath'));
		$instance->expects($this->any())->method('sanitizePath')->willReturnArgument(0);
		$instance->setTemplateRootPaths(array('1'));
		$instance->setLayoutRootPaths(array('2'));
		$instance->setPartialRootPaths(array('3'));
		$result = $instance->toArray();
		$expected = array(
			TemplatePaths::CONFIG_TEMPLATEROOTPATHS => array(1),
			TemplatePaths::CONFIG_LAYOUTROOTPATHS => array(2),
			TemplatePaths::CONFIG_PARTIALROOTPATHS => array(3)
		);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function testResolveFilesInFolders() {
		$instance = new TemplatePaths();
		$method = new \ReflectionMethod($instance, 'resolveFilesInFolders');
		$method->setAccessible(TRUE);
		$result = $method->invokeArgs(
			$instance,
			array(array('examples/Resources/Private/Layouts/', 'examples/Resources/Private/Templates/Default/'), 'html')
		);
		$this->assertEquals(
			array('examples/Resources/Private/Layouts/Default.html', 'examples/Resources/Private/Templates/Default/Default.html'),
			$result
		);
	}

	/**
	 * @test
	 */
	public function testGetTemplateSourceThrowsExceptionIfFileNotFound() {
		$instance = new TemplatePaths();
		$this->setExpectedException(InvalidTemplateResourceException::class);
		$instance->getTemplateSource();
	}

	/**
	 * @test
	 */
	public function testResolveFileInPathsThrowsExceptionIfFileNotFound() {
		$instance = new TemplatePaths();
		$method = new \ReflectionMethod($instance, 'resolveFileInPaths');
		$method->setAccessible(TRUE);
		$this->setExpectedException(InvalidTemplateResourceException::class);
		$method->invokeArgs($instance, array(array('/not/', '/found/'), 'notfound.html'));
	}

}
