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
		$result = $resolver->isNamespaceValid('test2');
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
		$resolver = $this->getAccessibleMock(ViewHelperResolver::class, array('dummy'));
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
	public function testAddNamespaceWithString() {
		$resolver = $this->getMock(ViewHelperResolver::class, array('dummy'));
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
	public function testAddNamespaceWithArray() {
		$resolver = $this->getMock(ViewHelperResolver::class, array('dummy'));
		$resolver->addNamespace('f', array('Foo\\Bar'));
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
	public function testAddNamespaces() {
		$resolver = $this->getMock(ViewHelperResolver::class, array('dummy'));
		$resolver->addNamespaces(array('f' => 'Foo\\Bar'));
		$this->assertAttributeEquals(array(
			'f' => array(
				'TYPO3Fluid\\Fluid\\ViewHelpers',
				'Foo\\Bar'
			)
		), 'namespaces', $resolver);
	}

	/**
	 * @param string $input
	 * @param string $expected
	 * @test
	 * @dataProvider getResolvePhpNamespaceFromFluidNamespaceTestValues
	 */
	public function testResolvePhpNamespaceFromFluidNamespace($input, $expected) {
		$resolver = new ViewHelperResolver();
		$this->assertEquals($expected, $resolver->resolvePhpNamespaceFromFluidNamespace($input));
	}

	public function getResolvePhpNamespaceFromFluidNamespaceTestValues() {
		return array(
			array('Foo\\Bar', 'Foo\\Bar\\ViewHelpers'),
			array('Foo\\Bar\\ViewHelpers', 'Foo\\Bar\\ViewHelpers'),
			array('http://typo3.org/ns/Foo/Bar/ViewHelpers', 'Foo\\Bar\\ViewHelpers'),
		);
	}

	/**
	 * @test
	 */
	public function testCreateViewHelperInstance() {
		$resolver = $this->getMock(
			ViewHelperResolver::class,
			array('resolveViewHelperClassName', 'createViewHelperInstanceFromClassName')
		);
		$resolver->expects($this->once())->method('resolveViewHelperClassName')->with('foo', 'bar')->willReturn('foobar');
		$resolver->expects($this->once())->method('createViewHelperInstanceFromClassName')->with('foobar')->willReturn('baz');
		$this->assertEquals('baz', $resolver->createViewHelperInstance('foo', 'bar'));
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

	/**
	 * @param array $namespaces
	 * @param string $subject
	 * @param boolean $expected
	 * @test
	 * @dataProvider getIsNamespaceValidTestValues
	 */
	public function testIsNamespaceValidOrIgnored(array $namespaces, $subject, $expected) {
		$resolver = new ViewHelperResolver();
		$resolver->setNamespaces($namespaces);
		$result = $resolver->isNamespaceValid($subject);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getIsNamespaceValidTestValues() {
		return array(
			array(array('foo' => NULL), 'foo', FALSE),
			array(array('foo' => array('test')), 'foo', TRUE),
			array(array('foo' => array('test')), 'foobar', FALSE),
			array(array('foo*' => NULL), 'foo', FALSE),
		);
	}

	/**
	 * @param array $namespaces
	 * @param string $subject
	 * @param boolean $expected
	 * @test
	 * @dataProvider getIsNamespaceIgnoredTestValues
	 */
	public function testIsNamespaceIgnored(array $namespaces, $subject, $expected) {
		$resolver = new ViewHelperResolver();
		$resolver->setNamespaces($namespaces);
		$result = $resolver->isNamespaceIgnored($subject);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getIsNamespaceIgnoredTestValues() {
		return array(
			array(array('foo' => NULL), 'foo', TRUE),
			array(array('foo' => array('test')), 'foo', FALSE),
			array(array('foo' => array('test')), 'foobar', FALSE),
			array(array('foo*' => NULL), 'foobar', TRUE),
		);
	}

	/**
	 * @param array $namespaces
	 * @param string $subject
	 * @param boolean $expected
	 * @test
	 * @dataProvider getIsNamespaceValidOrIgnoredTestValues
	 */
	public function testIsNamespaceValidOrIgnoredTestValues(array $namespaces, $subject, $expected) {
		$resolver = new ViewHelperResolver();
		$resolver->setNamespaces($namespaces);
		$result = $resolver->isNamespaceValidOrIgnored($subject);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getIsNamespaceValidOrIgnoredTestValues() {
		return array(
			array(array('foo' => NULL), 'foo', TRUE),
			array(array('foo' => array('test')), 'foo', TRUE),
			array(array('foo' => array('test')), 'foobar', FALSE),
			array(array('foo*' => NULL), 'foobar', TRUE),
		);
	}

}
