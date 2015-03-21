<?php
namespace TYPO3\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Class ViewHelperResolverTest
 */
class ViewHelperResolverTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function testRegisterNamespaceRecordsNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->registerNamespace('t', 'test');
		$this->assertAttributeContains('test', 'namespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function testRegisterNamespaceThrowsExceptionOnReRegistration() {
		$resolver = new ViewHelperResolver();
		$resolver->registerNamespace('t', 'test');
		$this->setExpectedException('TYPO3\\Fluid\\Core\\ViewHelper\\Exception');
		$resolver->registerNamespace('t', 'test2');
	}

	/**
	 * @test
	 */
	public function testSetNamespacesSetsNamespaces() {
		$resolver = new ViewHelperResolver();
		$resolver->setNamespaces(array('t' => 'test'));
		$this->assertAttributeEquals(array('t' => 'test'), 'namespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function testIgnoreNamespaceRecordsNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->ignoreNamespace('t');
		$this->assertAttributeEquals(array('t'), 'ignoredNamespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function testIsNamespaceReturnsFalseOnIgnoredNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->ignoreNamespace('/test/i');
		$resolver->ignoreNamespace('/test2/i');
		$result = $resolver->isNamespaceValid('test2', 'test');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testIsNamespaceThrowsExceptionIfNamespaceNeitherValidNorIgnored() {
		$resolver = new ViewHelperResolver();
		$this->setExpectedException('TYPO3\\Fluid\\Core\\ViewHelper\\Exception');
		$result = $resolver->isNamespaceValid('test2', 'test');
	}

	/**
	 * @test
	 */
	public function testResolveViewHelperClassNameThrowsExceptionIfClassNotResolved() {
		$resolver = $this->getMock('TYPO3\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('resolveViewHelperName'));
		$resolver->expects($this->once())->method('resolveViewHelperName')->willReturn(FALSE);
		$this->setExpectedException('TYPO3\\Fluid\\Core\\ViewHelper\\Exception');
		$resolver->resolveViewHelperClassName('f', 'invalid');
	}

}
