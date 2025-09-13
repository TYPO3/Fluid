<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\View\ViewInterface;
use TYPO3Fluid\Fluid\ViewHelpers\Format\TrimViewHelper;

final class ViewHelperVariableContainerTest extends TestCase
{
    #[Test]
    public function storedDataCanBeReadOutAgain(): void
    {
        $subject = new ViewHelperVariableContainer();
        $variable = 'Hello world';
        self::assertFalse($subject->exists(TestViewHelper::class, 'test'));
        $subject->add(TestViewHelper::class, 'test', $variable);
        self::assertTrue($subject->exists(TestViewHelper::class, 'test'));
        self::assertEquals($variable, $subject->get(TestViewHelper::class, 'test'));
    }

    #[Test]
    public function addOrUpdateSetsAKeyIfItDoesNotExistYet(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'nonExistentKey', 'value1');
        self::assertEquals($subject->get('Foo\Bar', 'nonExistentKey'), 'value1');
    }

    #[Test]
    public function addOrUpdateOverridesAnExistingKey(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'someKey', 'value1');
        $subject->addOrUpdate('Foo\Bar', 'someKey', 'value2');
        self::assertEquals($subject->get('Foo\Bar', 'someKey'), 'value2');
    }

    #[Test]
    public function aSetValueCanBeRemovedAgain(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'nonExistentKey', 'value1');
        $subject->remove('Foo\Bar', 'nonExistentKey');
        self::assertFalse($subject->exists('Foo\Bar', 'nonExistentKey'));
    }

    #[Test]
    public function existsReturnsFalseIfTheSpecifiedKeyDoesNotExist(): void
    {
        $subject = new ViewHelperVariableContainer();
        self::assertFalse($subject->exists('Foo\Bar', 'nonExistentKey'));
    }

    #[Test]
    public function existsReturnsTrueIfTheSpecifiedKeyExists(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'someKey', 'someValue');
        self::assertTrue($subject->exists('Foo\Bar', 'someKey'));
    }

    #[Test]
    public function existsReturnsTrueIfTheSpecifiedKeyExistsAndIsNull(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add('Foo\Bar', 'someKey', null);
        self::assertTrue($subject->exists('Foo\Bar', 'someKey'));
    }

    #[Test]
    public function getViewReturnsPreviouslySetView(): void
    {
        $subject = new ViewHelperVariableContainer();
        $view = $this->createMock(ViewInterface::class);
        $subject->setView($view);
        self::assertSame($view, $subject->getView());
    }

    #[Test]
    public function getAllGetsAllVariables(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->addAll('Foo\\Bar', ['foo' => 'foo', 'bar' => 'bar']);
        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $subject->getAll('Foo\\Bar'));
    }

    #[Test]
    public function getAllReturnsDefaultIfNotFound(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->addAll('Foo\\Bar', ['foo' => 'foo']);
        self::assertSame(['foo' => 'bar'], $subject->getAll('Baz\\Baz', ['foo' => 'bar']));
    }

    #[Test]
    public function serializeContainsObjects(): void
    {
        $subject = new ViewHelperVariableContainer();
        $subject->add(TrimViewHelper::class, 'foo', 'bar');
        $serialized = serialize($subject);
        $unserialized = unserialize($serialized);
        self::assertSame(['foo' => 'bar'], $unserialized->getAll(TrimViewHelper::class));
    }

    #[Test]
    public function getReturnsDefaultIfRequestedVariableDoesNotExist(): void
    {
        $subject = new ViewHelperVariableContainer();
        self::assertEquals('test', $subject->get('foo', 'bar', 'test'));
    }
}
