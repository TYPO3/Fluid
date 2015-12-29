<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
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
		$this->setExpectedException(Exception::class);
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
		$resolver = $this->getMock(ViewHelperResolver::class, array('resolveViewHelperName'));
		$resolver->expects($this->once())->method('resolveViewHelperName')->willReturn(FALSE);
		$this->setExpectedException(Exception::class);
		$resolver->resolveViewHelperClassName('f', 'invalid');
	}

	/**
	 * @test
	 */
	public function testResolveViewHelperClassNameSupportsMultipleNamespaces() {
		$resolver = $this->getAccessibleMock('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('dummy'));
		$resolver->_set('namespaces', array(
			'f' => array(
				'FluidTYPO3\\Fluid\\ViewHelpers',
				'Foo\\Bar'
			)
		));
		$result = $resolver->_call('resolveViewHelperName', 'f', 'render');
		$this->assertEquals('FluidTYPO3\\Fluid\\ViewHelpers\\RenderViewHelper', $result);
	}

	/**
	 * @test
	 */
	public function testExtendNamespace() {
		$resolver = $this->getMock('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('dummy'));
		$resolver->extendNamespace('f', 'Foo\\Bar');
		$this->assertAttributeEquals(array(
			'f' => array(
				'TYPO3Fluid\\Fluid\\ViewHelpers',
				'Foo\\Bar'
			)
		), 'namespaces', $resolver);
	}

	/**
	 * @test
	 * @expectedException \TYPO3Fluid\Fluid\Core\Parser\Exception
	 */
	public function registerNamespaceThrowsExceptionIfOneAliasIsRegisteredWithDifferentPhpNamespaces() {
		$resolver = new ViewHelperResolver();
		$resolver->registerNamespace('foo', 'Some\Namespace');
		$resolver->registerNamespace('foo', 'Some\Other\Namespace');
	}

	/**
	 * @test
	 */
	public function registerNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->registerNamespace('foo', 'Some\Namespace');
		$this->assertAttributeEquals(array('f' => 'TYPO3Fluid\Fluid\ViewHelpers', 'foo' => 'Some\Namespace'), 'namespaces', $resolver);
		$resolver->registerNamespace('foo', 'Some\Namespace');
		$this->assertAttributeEquals(array('f' => 'TYPO3Fluid\Fluid\ViewHelpers', 'foo' => 'Some\Namespace'), 'namespaces', $resolver);
	}

}
