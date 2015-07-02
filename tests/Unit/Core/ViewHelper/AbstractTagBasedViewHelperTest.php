<?php
namespace NamelessCoder\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\ViewHelper\TagBuilder;
use NamelessCoder\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TagBasedViewHelper
 */
class AbstractTagBasedViewHelperTest extends UnitTestCase {

	public function setUp() {
		$this->viewHelper = $this->getAccessibleMock('NamelessCoder\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper', array('dummy'), array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function testConstructorSetsTagBuilder() {
		$viewHelper = $this->getAccessibleMock(
			'NamelessCoder\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper',
			array('dummy'),
			array(), '', TRUE
		);
		$this->assertAttributeInstanceOf('NamelessCoder\\Fluid\\Core\\ViewHelper\\TagBuilder', 'tag', $viewHelper);
	}

	/**
	 * @test
	 */
	public function testSetTagBuilderSetsTagBuilder() {
		$viewHelper = $this->getAccessibleMock(
			'NamelessCoder\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper',
			array('dummy'),
			array(), '', FALSE
		);
		$tagBuilder = new TagBuilder('div');
		$viewHelper->setTagBuilder($tagBuilder);
		$this->assertAttributeSame($tagBuilder, 'tag', $viewHelper);
	}

	/**
	 * @test
	 */
	public function testRenderCallsRenderOnTagBuilder() {
		$viewHelper = $this->getAccessibleMock(
			'NamelessCoder\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper',
			array('dummy'),
			array(), '', FALSE
		);
		$tagBuilder = $this->getMock('NamelessCoder\\Fluid\\Core\\ViewHelper\\TagBuilder', array('render'));
		$tagBuilder->expects($this->once())->method('render')->willReturn('foobar');
		$viewHelper->setTagBuilder($tagBuilder);
		$this->assertEquals('foobar', $viewHelper->render());
	}

	/**
	 * @test
	 */
	public function testInitializeArgumentsRegistersExpectedArguments() {
		$viewHelper = $this->getAccessibleMock(
			'NamelessCoder\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper',
			array('registerArgument'),
			array(), '', FALSE
		);
		$viewHelper->expects($this->at(0))->method('registerArgument')->with('additionalAttributes');
		$viewHelper->expects($this->at(1))->method('registerArgument')->with('data');
		$viewHelper->initializeArguments();
	}

	/**
	 * @test
	 */
	public function oneTagAttributeIsRenderedCorrectly() {
		$mockTagBuilder = $this->getMock('NamelessCoder\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('foo', 'bar');
		$this->viewHelper->setTagBuilder($mockTagBuilder);

		$this->viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', FALSE);
		$arguments = array('foo' => 'bar');
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 */
	public function additionalTagAttributesAreRenderedCorrectly() {
		$mockTagBuilder = $this->getMock('NamelessCoder\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('foo', 'bar');
		$this->viewHelper->setTagBuilder($mockTagBuilder);

		$this->viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', FALSE);
		$arguments = array('additionalAttributes' => array('foo' => 'bar'));
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 */
	public function dataAttributesAreRenderedCorrectly() {
		$mockTagBuilder = $this->getMock('NamelessCoder\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('data-foo', 'bar');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('data-baz', 'foos');
		$this->viewHelper->setTagBuilder($mockTagBuilder);

		$arguments = array('data' => array('foo' => 'bar', 'baz' => 'foos'));
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initialize();
	}

	/**
	 * @test
	 */
	public function standardTagAttributesAreRegistered() {
		$mockTagBuilder = $this->getMock('NamelessCoder\Fluid\Core\ViewHelper\TagBuilder', array('addAttribute'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('class', 'classAttribute');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('dir', 'dirAttribute');
		$mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('id', 'idAttribute');
		$mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('lang', 'langAttribute');
		$mockTagBuilder->expects($this->at(4))->method('addAttribute')->with('style', 'styleAttribute');
		$mockTagBuilder->expects($this->at(5))->method('addAttribute')->with('title', 'titleAttribute');
		$mockTagBuilder->expects($this->at(6))->method('addAttribute')->with('accesskey', 'accesskeyAttribute');
		$mockTagBuilder->expects($this->at(7))->method('addAttribute')->with('tabindex', 'tabindexAttribute');
		$this->viewHelper->setTagBuilder($mockTagBuilder);

		$arguments = array(
			'class' => 'classAttribute',
			'dir' => 'dirAttribute',
			'id' => 'idAttribute',
			'lang' => 'langAttribute',
			'style' => 'styleAttribute',
			'title' => 'titleAttribute',
			'accesskey' => 'accesskeyAttribute',
			'tabindex' => 'tabindexAttribute'
		);
		$this->viewHelper->_call('registerUniversalTagAttributes');
		$this->viewHelper->setArguments($arguments);
		$this->viewHelper->initializeArguments();
		$this->viewHelper->initialize();
	}
}
