<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

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
		$this->setExpectedException('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\Exception');
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
	public function testIsNamespaceReturnsFalseIfNamespaceNotValid() {
		$resolver = new ViewHelperResolver();
		$result = $resolver->isNamespaceValid('test2', 'test');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testResolveViewHelperClassNameThrowsExceptionIfClassNotResolved() {
		$resolver = $this->getMock('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('resolveViewHelperName'));
		$resolver->expects($this->once())->method('resolveViewHelperName')->willReturn(FALSE);
		$this->setExpectedException('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\Exception');
		$resolver->resolveViewHelperClassName('f', 'invalid');
	}

}
