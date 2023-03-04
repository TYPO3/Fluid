<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;

class ViewHelperVariableContainerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function storedDataCanBeReadOutAgain(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $variable = 'Hello world';
        self::assertFalse($viewHelperVariableContainer->exists(TestViewHelper::class, 'test'));
        $viewHelperVariableContainer->add(TestViewHelper::class, 'test', $variable);
        self::assertTrue($viewHelperVariableContainer->exists(TestViewHelper::class, 'test'));

        self::assertEquals($variable, $viewHelperVariableContainer->get(TestViewHelper::class, 'test'));
    }

    /**
     * @test
     */
    public function addOrUpdateSetsAKeyIfItDoesNotExistYet(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->add('Foo\Bar', 'nonExistentKey', 'value1');
        self::assertEquals($viewHelperVariableContainer->get('Foo\Bar', 'nonExistentKey'), 'value1');
    }

    /**
     * @test
     */
    public function addOrUpdateOverridesAnExistingKey(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->add('Foo\Bar', 'someKey', 'value1');
        $viewHelperVariableContainer->addOrUpdate('Foo\Bar', 'someKey', 'value2');
        self::assertEquals($viewHelperVariableContainer->get('Foo\Bar', 'someKey'), 'value2');
    }

    /**
     * @test
     */
    public function aSetValueCanBeRemovedAgain(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->add('Foo\Bar', 'nonExistentKey', 'value1');
        $viewHelperVariableContainer->remove('Foo\Bar', 'nonExistentKey');
        self::assertFalse($viewHelperVariableContainer->exists('Foo\Bar', 'nonExistentKey'));
    }

    /**
     * @test
     */
    public function existsReturnsFalseIfTheSpecifiedKeyDoesNotExist(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        self::assertFalse($viewHelperVariableContainer->exists('Foo\Bar', 'nonExistentKey'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedKeyExists(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->add('Foo\Bar', 'someKey', 'someValue');
        self::assertTrue($viewHelperVariableContainer->exists('Foo\Bar', 'someKey'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedKeyExistsAndIsNull(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->add('Foo\Bar', 'someKey', null);
        self::assertTrue($viewHelperVariableContainer->exists('Foo\Bar', 'someKey'));
    }

    /**
     * @test
     */
    public function viewCanBeReadOutAgain(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $view = $this->getMockForAbstractClass(AbstractTemplateView::class);
        $viewHelperVariableContainer->setView($view);
        self::assertSame($view, $viewHelperVariableContainer->getView());
    }

    /**
     * @test
     */
    public function getAllGetsAllVariables(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->addAll('Foo\\Bar', ['foo' => 'foo', 'bar' => 'bar']);
        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $viewHelperVariableContainer->getAll('Foo\\Bar'));
    }

    /**
     * @test
     */
    public function getAllReturnsDefaultIfNotFound(): void
    {
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->addAll('Foo\\Bar', ['foo' => 'foo']);
        self::assertSame(['foo' => 'bar'], $viewHelperVariableContainer->getAll('Baz\\Baz', ['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function addAllThrowsInvalidArgumentExceptionOnUnsupportedType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $viewHelperVariableContainer->addAll('Foo\\Bar', new \DateTime('now'));
    }

    /**
     * @test
     */
    public function testSleepReturnsExpectedPropertyNames(): void
    {
        $subject = new ViewHelperVariableContainer();
        $properties = $subject->__sleep();
        self::assertContains('objects', $properties);
    }

    /**
     * @test
     */
    public function testGetReturnsDefaultIfRequestedVariableDoesNotExist(): void
    {
        $subject = new ViewHelperVariableContainer();
        self::assertEquals('test', $subject->get('foo', 'bar', 'test'));
    }
}
