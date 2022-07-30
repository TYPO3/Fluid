<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;

/**
 * Testcase for AbstractViewHelper
 */
class ViewHelperVariableContainerTest extends UnitTestCase
{

    /**
     * @var ViewHelperVariableContainer
     */
    protected $viewHelperVariableContainer;

    protected function setUp(): void
    {
        $this->viewHelperVariableContainer = new ViewHelperVariableContainer();
    }

    /**
     * @test
     */
    public function storedDataCanBeReadOutAgain()
    {
        $variable = 'Hello world';
        self::assertFalse($this->viewHelperVariableContainer->exists(TestViewHelper::class, 'test'));
        $this->viewHelperVariableContainer->add(TestViewHelper::class, 'test', $variable);
        self::assertTrue($this->viewHelperVariableContainer->exists(TestViewHelper::class, 'test'));

        self::assertEquals($variable, $this->viewHelperVariableContainer->get(TestViewHelper::class, 'test'));
    }

    /**
     * @test
     */
    public function addOrUpdateSetsAKeyIfItDoesNotExistYet()
    {
        $this->viewHelperVariableContainer->add('Foo\Bar', 'nonExistentKey', 'value1');
        self::assertEquals($this->viewHelperVariableContainer->get('Foo\Bar', 'nonExistentKey'), 'value1');
    }

    /**
     * @test
     */
    public function addOrUpdateOverridesAnExistingKey()
    {
        $this->viewHelperVariableContainer->add('Foo\Bar', 'someKey', 'value1');
        $this->viewHelperVariableContainer->addOrUpdate('Foo\Bar', 'someKey', 'value2');
        self::assertEquals($this->viewHelperVariableContainer->get('Foo\Bar', 'someKey'), 'value2');
    }

    /**
     * @test
     */
    public function aSetValueCanBeRemovedAgain()
    {
        $this->viewHelperVariableContainer->add('Foo\Bar', 'nonExistentKey', 'value1');
        $this->viewHelperVariableContainer->remove('Foo\Bar', 'nonExistentKey');
        self::assertFalse($this->viewHelperVariableContainer->exists('Foo\Bar', 'nonExistentKey'));
    }

    /**
     * @test
     */
    public function existsReturnsFalseIfTheSpecifiedKeyDoesNotExist()
    {
        self::assertFalse($this->viewHelperVariableContainer->exists('Foo\Bar', 'nonExistentKey'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedKeyExists()
    {
        $this->viewHelperVariableContainer->add('Foo\Bar', 'someKey', 'someValue');
        self::assertTrue($this->viewHelperVariableContainer->exists('Foo\Bar', 'someKey'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedKeyExistsAndIsNull()
    {
        $this->viewHelperVariableContainer->add('Foo\Bar', 'someKey', null);
        self::assertTrue($this->viewHelperVariableContainer->exists('Foo\Bar', 'someKey'));
    }

    /**
     * @test
     */
    public function viewCanBeReadOutAgain()
    {
        $view = $this->getMockForAbstractClass(AbstractTemplateView::class);
        $this->viewHelperVariableContainer->setView($view);
        self::assertSame($view, $this->viewHelperVariableContainer->getView());
    }

    /**
     * @test
     */
    public function getAllGetsAllVariables()
    {
        $this->viewHelperVariableContainer->addAll('Foo\\Bar', ['foo' => 'foo', 'bar' => 'bar']);
        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $this->viewHelperVariableContainer->getAll('Foo\\Bar'));
    }

    /**
     * @test
     */
    public function getAllReturnsDefaultIfNotFound()
    {
        $this->viewHelperVariableContainer->addAll('Foo\\Bar', ['foo' => 'foo']);
        self::assertSame(['foo' => 'bar'], $this->viewHelperVariableContainer->getAll('Baz\\Baz', ['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function addAllThrowsInvalidArgumentExceptionOnUnsupportedType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->viewHelperVariableContainer->addAll('Foo\\Bar', new \DateTime('now'));
    }

    /**
     * @test
     */
    public function testSleepReturnsExpectedPropertyNames()
    {
        $subject = new ViewHelperVariableContainer();
        $properties = $subject->__sleep();
        self::assertContains('objects', $properties);
    }

    /**
     * @test
     */
    public function testGetReturnsDefaultIfRequestedVariableDoesNotExist()
    {
        $subject = new ViewHelperVariableContainer();
        self::assertEquals('test', $subject->get('foo', 'bar', 'test'));
    }
}
