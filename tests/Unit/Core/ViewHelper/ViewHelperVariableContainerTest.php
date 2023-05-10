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
use TYPO3Fluid\Fluid\View\ViewInterface;

class ViewHelperVariableContainerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function storedDataCanBeReadOutAgain(): void
    {
        $subject = new ViewHelperVariableContainer();
        $variable = 'Hello world';
        self::assertFalse($subject->exists(TestViewHelper::class, 'test'));
        $subject->add(TestViewHelper::class, 'test', $variable);
        self::assertTrue($subject->exists(TestViewHelper::class, 'test'));
        self::assertEquals($variable, $subject->get(TestViewHelper::class, 'test'));
    }

    /**
     * @test
     */
    public function addOrUpdateSetsAKeyIfItDoesNotExistYet(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'nonExistentKey', 'value1');
        self::assertEquals($subject->get('Foo\Bar', 'nonExistentKey'), 'value1');
    }

    /**
     * @test
     */
    public function addOrUpdateOverridesAnExistingKey(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'someKey', 'value1');
        $subject->addOrUpdate('Foo\Bar', 'someKey', 'value2');
        self::assertEquals($subject->get('Foo\Bar', 'someKey'), 'value2');
    }

    /**
     * @test
     */
    public function aSetValueCanBeRemovedAgain(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'nonExistentKey', 'value1');
        $subject->remove('Foo\Bar', 'nonExistentKey');
        self::assertFalse($subject->exists('Foo\Bar', 'nonExistentKey'));
    }

    /**
     * @test
     */
    public function existsReturnsFalseIfTheSpecifiedKeyDoesNotExist(): void
    {
        $subject = new ViewHelperVariableContainer();
        self::assertFalse($subject->exists('Foo\Bar', 'nonExistentKey'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedKeyExists(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'someKey', 'someValue');
        self::assertTrue($subject->exists('Foo\Bar', 'someKey'));
    }

    /**
     * @test
     */
    public function existsReturnsTrueIfTheSpecifiedKeyExistsAndIsNull(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'someKey', null);
        self::assertTrue($subject->exists('Foo\Bar', 'someKey'));
    }

    /**
     * @test
     */
    public function getViewReturnsPreviouslySetView(): void
    {
        $subject = new ViewHelperVariableContainer();
        $view = $this->createMock(ViewInterface::class);
        $subject->setView($view);
        self::assertSame($view, $subject->getView());
    }

    /**
     * @test
     */
    public function getAllGetsAllVariables(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->addAll('Foo\\Bar', ['foo' => 'foo', 'bar' => 'bar']);
        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $subject->getAll('Foo\\Bar'));
    }

    /**
     * @test
     */
    public function getAllReturnsDefaultIfNotFound(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->addAll('Foo\\Bar', ['foo' => 'foo']);
        self::assertSame(['foo' => 'bar'], $subject->getAll('Baz\\Baz', ['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function addAllThrowsInvalidArgumentExceptionOnUnsupportedType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $subject = new ViewHelperVariableContainer();
        $subject->addAll('Foo\\Bar', new \DateTime('now'));
    }

    /**
     * @test
     */
    public function sleepReturnsExpectedPropertyNames(): void
    {
        $subject = new ViewHelperVariableContainer();
        $properties = $subject->__sleep();
        self::assertContains('objects', $properties);
    }

    /**
     * @test
     */
    public function getReturnsDefaultIfRequestedVariableDoesNotExist(): void
    {
        $subject = new ViewHelperVariableContainer();
        self::assertEquals('test', $subject->get('foo', 'bar', 'test'));
    }
}
