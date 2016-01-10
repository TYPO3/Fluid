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
	public function testAddNamespaceWithStringRecordsNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->addNamespace('t', 'test');
		$this->assertAttributeContains(array('test'), 'namespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function testAddNamespaceWithArrayRecordsNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->addNamespace('t', array('test'));
		$this->assertAttributeContains(array('test'), 'namespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function testSetNamespacesSetsNamespaces() {
		$resolver = new ViewHelperResolver();
		$resolver->setNamespaces(array('t' => array('test')));
		$this->assertAttributeEquals(array('t' => array('test')), 'namespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function testSetNamespacesSetsNamespacesAndConvertsStringNamespaceToArray() {
		$resolver = new ViewHelperResolver();
		$resolver->setNamespaces(array('t' => 'test'));
		$this->assertAttributeEquals(array('t' => array('test')), 'namespaces', $resolver);
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
	public function testAddNamespace() {
		$resolver = $this->getMock('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperResolver', array('dummy'));
		$resolver->addNamespace('f', 'Foo\\Bar');
		$this->assertAttributeEquals(array(
			'f' => array(
				'TYPO3Fluid\\Fluid\\ViewHelpers',
				'Foo\\Bar'
			)
		), 'namespaces', $resolver);
	}

	/**
	 * @test
	 */
	public function addNamespaceDoesNotThrowAnExceptionIfTheAliasExistAlreadyAndPointsToTheSamePhpNamespace() {
		$resolver = new ViewHelperResolver();
		$resolver->addNamespace('foo', 'Some\Namespace');
		$this->assertAttributeEquals(array('f' => array('TYPO3Fluid\Fluid\ViewHelpers'), 'foo' => array('Some\Namespace')), 'namespaces', $resolver);
		$resolver->addNamespace('foo', 'Some\Namespace');
		$this->assertAttributeEquals(array('f' => array('TYPO3Fluid\Fluid\ViewHelpers'), 'foo' => array('Some\Namespace')), 'namespaces', $resolver);
	}

}
