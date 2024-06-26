<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Schema;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Schema\ViewHelperMetadataFactory;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\Sub\ArbitraryArgumentsViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\Sub\DeprecatedViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\WithDocumentationViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\WithoutClassSuffix;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\WithoutDocumentationViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\ViewHelpers\WithoutInterfaceViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Schema\Fixtures\WrongDirectoryViewHelper;

class ViewHelperMetadataFactoryTest extends TestCase
{
    public static function createObjectDataProvider(): iterable
    {
        return [
            'WithoutDocumentationViewHelper' => [
                WithoutDocumentationViewHelper::class,
                'TYPO3Fluid\\Fluid\\Tests\\Unit\\Schema\\Fixtures\\ViewHelpers',
                'WithoutDocumentationViewHelper',
                'withoutDocumentation',
                '',
                'http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Unit/Schema/Fixtures/ViewHelpers',
                [],
                false,
            ],
            'WithDocumentationViewHelper' => [
                WithDocumentationViewHelper::class,
                'TYPO3Fluid\\Fluid\\Tests\\Unit\\Schema\\Fixtures\\ViewHelpers',
                'WithDocumentationViewHelper',
                'withDocumentation',
                "This is an example documentation with multiple lines\nof text.\n\nExamples\n========\n\nWe usually have some examples\n\n::\n   <demo:withDocumentation value=\"test\" />",
                'http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Unit/Schema/Fixtures/ViewHelpers',
                ['@internal' => ''],
                false,
            ],
            'DeprecatedViewHelper' => [
                DeprecatedViewHelper::class,
                'TYPO3Fluid\\Fluid\\Tests\\Unit\\Schema\\Fixtures\\ViewHelpers',
                'Sub\\DeprecatedViewHelper',
                'sub.deprecated',
                '',
                'http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Unit/Schema/Fixtures/ViewHelpers',
                ['@deprecated' => 'this is a deprecation message'],
                false,
            ],
            'ArbitraryArgumentsViewHelper' => [
                ArbitraryArgumentsViewHelper::class,
                'TYPO3Fluid\\Fluid\\Tests\\Unit\\Schema\\Fixtures\\ViewHelpers',
                'Sub\\ArbitraryArgumentsViewHelper',
                'sub.arbitraryArguments',
                '',
                'http://typo3.org/ns/TYPO3Fluid/Fluid/Tests/Unit/Schema/Fixtures/ViewHelpers',
                [],
                true,
            ],
        ];
    }

    #[Test]
    #[DataProvider('createObjectDataProvider')]
    public function createObject(
        string $className,
        string $namespace,
        string $name,
        string $tagName,
        string $documentation,
        string $xmlNamespace,
        array $docTags,
        bool $allowsArbitraryArguments,
    ): void {
        $object = (new ViewHelperMetadataFactory())->createFromViewHelperClass($className);
        self::assertSame($className, $object->className);
        self::assertSame($namespace, $object->namespace);
        self::assertSame($name, $object->name);
        self::assertSame($tagName, $object->tagName);
        self::assertSame($documentation, $object->documentation);
        self::assertSame($xmlNamespace, $object->xmlNamespace);
        self::assertSame($docTags, $object->docTags);
        self::assertSame($allowsArbitraryArguments, $object->allowsArbitraryArguments);
        self::assertEquals(
            ['value' => new ArgumentDefinition('value', 'string', 'A test argument', false)],
            $object->argumentDefinitions,
        );
    }

    public static function createObjectFailureDataProvider(): iterable
    {
        return [
            'NonexistentViewHelper' => ['TYPO3Fluid\\Fluid\\Tests\\Unit\\Schema\\Fixtures\\ViewHelpers\\NonexistentViewHelper'],
            'WithoutInterfaceViewHelper' => [WithoutInterfaceViewHelper::class],
            'WithoutClassSuffix' => [WithoutClassSuffix::class],
            'AbstractViewHelper' => [AbstractViewHelper::class],
            'WrongDirectoryViewHelper' => [WrongDirectoryViewHelper::class],
        ];
    }

    #[Test]
    #[DataProvider('createObjectFailureDataProvider')]
    public function createObjectFailure(string $className): void
    {
        self::expectException(\InvalidArgumentException::class);
        (new ViewHelperMetadataFactory())->createFromViewhelperClass($className);
    }
}
