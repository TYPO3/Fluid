<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\View\TemplateView;

final class GroupedForViewHelperTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function renderThrowsExceptionWhenEachIsNotTraversable(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1253108907);
        $source = '<f:groupedFor each="{items}" as="group" groupBy="by"></f:groupedFor>';

        $view = new TemplateView();
        $view->assign('items', new ArrayAccessExample([]));
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

    #[Test]
    public function renderThrowsExceptionWhenEachIsOneDimensionalArray(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1253120365);
        $source = '<f:groupedFor each="{items}" as="group" groupBy="by"></f:groupedFor>';

        $view = new TemplateView();
        $view->assign('items', ['some', 'simple', 'array']);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->render();
    }

    #[Test]
    public function renderReturnsEmptyStringWhenEachIsNull(): void
    {
        $source = '<f:groupedFor each="{items}" as="group" groupBy="by"></f:groupedFor>';

        $view = new TemplateView();
        $view->assign('items', null);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame('', $view->render());
    }

    public static function renderDataProvider(): \Generator
    {
        yield 'empty each returns empty string' => [
            '<f:groupedFor each="{}" as="foo" groupBy="bar">'
                . '<f:for each="{foo}" as="item">{item}</f:for>'
            . '</f:groupedFor>',
            [],
            '',
        ];

        yield 'group multidimensional array and preserve keys' => [
            '<f:groupedFor each="{products}" as="products" groupBy="license" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey}' . chr(10)
                . '<f:for each="{products}" as="product" key="productKey">'
                    . '{productKey}: {product.license}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'products' => [
                    'photoshop' => ['name' => 'Adobe Photoshop', 'license' => 'commercial'],
                    'typo3' => ['name' => 'TYPO3', 'license' => 'GPL'],
                    'office' => ['name' => 'Microsoft Office', 'license' => 'commercial'],
                    'drupal' => ['name' => 'Drupal', 'license' => 'GPL'],
                    'wordpress' => ['name' => 'Wordpress', 'license' => 'GPL'],
                ],
            ],
            'groupKey: commercial' . chr(10)
            . 'photoshop: commercial' . chr(10)
            . 'office: commercial' . chr(10)
            . 'groupKey: GPL' . chr(10)
            . 'typo3: GPL' . chr(10)
            . 'drupal: GPL' . chr(10)
            . 'wordpress: GPL' . chr(10),
        ];

        $photoshop = new \stdClass();
        $photoshop->name = 'Adobe Photoshop';
        $photoshop->license = 'commercial';
        $typo3 = new \stdClass();
        $typo3->name = 'TYPO3';
        $typo3->license = 'GPL';
        $office = new \stdClass();
        $office->name = 'Microsoft Office';
        $office->license = 'commercial';
        $drupal = new \stdClass();
        $drupal->name = 'Drupal';
        $drupal->license = 'GPL';
        $wordpress = new \stdClass();
        $wordpress->name = 'Wordpress';
        $wordpress->license = 'GPL';
        yield 'group array of objects and preserve keys' => [
            '<f:groupedFor each="{products}" as="products" groupBy="license" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey}' . chr(10)
                . '<f:for each="{products}" as="product" key="productKey">'
                    . '{productKey}: {product.license}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'products' => [
                    'photoshop' => $photoshop,
                    'typo3' => $typo3,
                    'office' => $office,
                    'drupal' => $drupal,
                    'wordpress' => $wordpress,
                ],
            ],
            'groupKey: commercial' . chr(10)
            . 'photoshop: commercial' . chr(10)
            . 'office: commercial' . chr(10)
            . 'groupKey: GPL' . chr(10)
            . 'typo3: GPL' . chr(10)
            . 'drupal: GPL' . chr(10)
            . 'wordpress: GPL' . chr(10),
        ];

        $photoshop = new \stdClass();
        $photoshop->name = 'Adobe Photoshop';
        $photoshop->license = 'commercial';
        $typo3 = new \stdClass();
        $typo3->name = 'TYPO3';
        $typo3->license = 'GPL';
        $office = new \stdClass();
        $office->name = 'Microsoft Office';
        $office->license = 'commercial';
        $drupal = new \stdClass();
        $drupal->name = 'Drupal';
        $drupal->license = 'GPL';
        $wordpress = new \stdClass();
        $wordpress->name = 'Wordpress';
        $wordpress->license = 'GPL';
        yield 'group iterator of objects and preserve keys' => [
            '<f:groupedFor each="{products}" as="products" groupBy="license" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey}' . chr(10)
                . '<f:for each="{products}" as="product" key="productKey">'
                    . '{productKey}: {product.license}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'products' => new \ArrayIterator([
                    'photoshop' => $photoshop,
                    'typo3' => $typo3,
                    'office' => $office,
                    'drupal' => $drupal,
                    'wordpress' => $wordpress,
                ]),
            ],
            'groupKey: commercial' . chr(10)
            . 'photoshop: commercial' . chr(10)
            . 'office: commercial' . chr(10)
            . 'groupKey: GPL' . chr(10)
            . 'typo3: GPL' . chr(10)
            . 'drupal: GPL' . chr(10)
            . 'wordpress: GPL' . chr(10),
        ];

        $customer1 = new \stdClass();
        $customer1->name = 'Anton Abel';
        $customer2 = new \stdClass();
        $customer2->name = 'Balthasar Bux';
        $invoice1 = ['date' => new \DateTime('1980-12-13'), 'customer' => $customer1];
        $invoice2 = ['date' => new \DateTime('2010-07-01'), 'customer' => $customer2];
        $invoice3 = ['date' => new \DateTime('2010-07-04'), 'customer' => $customer1];
        yield 'group multidimensional array by object key' => [
            '<f:groupedFor each="{invoices}" as="invoices" groupBy="customer" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey.name}' . chr(10)
                . '<f:for each="{invoices}" as="invoice">'
                    . '{invoice.customer.name}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'invoices' => [
                    'invoice1' => $invoice1,
                    'invoice2' => $invoice2,
                    'invoice3' => $invoice3,
                ],
            ],
            'groupKey: Anton Abel' . chr(10)
            . 'Anton Abel' . chr(10)
            . 'Anton Abel' . chr(10)
            . 'groupKey: Balthasar Bux' . chr(10)
            . 'Balthasar Bux' . chr(10),
        ];

        yield 'group multidimensional array by sub array key' => [
            '<f:groupedFor each="{products}" as="products" groupBy="license.theLicense" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey}' . chr(10)
                . '<f:for each="{products}" as="product" key="productKey">'
                    . '{productKey}: {product.name}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'products' => [
                    'photoshop' => ['name' => 'Adobe Photoshop', 'license' => ['theLicense' => 'commercial']],
                    'typo3' => ['name' => 'TYPO3', 'license' => ['theLicense' => 'GPL']],
                    'office' => ['name' => 'Microsoft Office', 'license' => ['theLicense' => 'commercial']],
                    'drupal' => ['name' => 'Drupal', 'license' => ['theLicense' => 'GPL']],
                    'wordpress' => ['name' => 'Wordpress', 'license' => ['theLicense' => 'GPL']],
                ],
            ],
            'groupKey: commercial' . chr(10)
            . 'photoshop: Adobe Photoshop' . chr(10)
            . 'office: Microsoft Office' . chr(10)
            . 'groupKey: GPL' . chr(10)
            . 'typo3: TYPO3' . chr(10)
            . 'drupal: Drupal' . chr(10)
            . 'wordpress: Wordpress' . chr(10),
        ];

        $customer1 = new \stdClass();
        $customer1->name = 'Anton Abel';
        $customer2 = new \stdClass();
        $customer2->name = 'Balthasar Bux';
        $invoice1 = ['date' => new \DateTime('1980-12-13'), 'customer' => $customer1];
        $invoice2 = ['date' => new \DateTime('2010-07-01'), 'customer' => $customer2];
        $invoice3 = ['date' => new \DateTime('2010-07-04'), 'customer' => $customer1];
        yield 'group multidimensional array by object property path' => [
            '<f:groupedFor each="{invoices}" as="invoices" groupBy="customer.name">'
                . '<f:for each="{invoices}" as="invoice">'
                    . '{invoice.customer.name}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'invoices' => [
                    'invoice1' => $invoice1,
                    'invoice2' => $invoice2,
                    'invoice3' => $invoice3,
                ],
            ],
            'Anton Abel' . chr(10)
            . 'Anton Abel' . chr(10)
            . 'Balthasar Bux' . chr(10),
        ];

        $customer1 = new \stdClass();
        $customer1->name = 'Anton Abel';
        $customer2 = new \stdClass();
        $customer2->name = 'Balthasar Bux';
        $invoice1 = new \stdClass();
        $invoice1->date = new \DateTime('1980-12-13');
        $invoice1->customer = $customer1;
        $invoice2 = new \stdClass();
        $invoice2->date = new \DateTime('2010-07-01');
        $invoice2->customer = $customer2;
        $invoice3 = new \stdClass();
        $invoice3->date = new \DateTime('2010-07-04');
        $invoice3->customer = $customer1;
        yield 'group object by child object key' => [
            '<f:groupedFor each="{invoices}" as="invoices" groupBy="customer" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey.name}' . chr(10)
                . '<f:for each="{invoices}" as="invoice">'
                    . '{invoice.customer.name}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'invoices' => [
                    'invoice1' => $invoice1,
                    'invoice2' => $invoice2,
                    'invoice3' => $invoice3,
                ],
            ],
            'groupKey: Anton Abel' . chr(10)
            . 'Anton Abel' . chr(10)
            . 'Anton Abel' . chr(10)
            . 'groupKey: Balthasar Bux' . chr(10)
            . 'Balthasar Bux' . chr(10),
        ];

        $invoice1->date = new \DateTime('1980-12-13', new \DateTimeZone('UTC'));
        $invoice1->id = 1;
        $invoice2 = new \stdClass();
        $invoice2->date = new \DateTime('2010-07-04', new \DateTimeZone('UTC'));
        $invoice2->id = 2;
        $invoice3 = new \stdClass();
        $invoice3->date = new \DateTime('1980-12-13', new \DateTimeZone('UTC'));
        $invoice3->id = 3;
        yield 'group multidimensional array by child DateTime object' => [
            '<f:groupedFor each="{invoices}" as="invoices" groupBy="date" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey.timestamp}' . chr(10)
                . '<f:for each="{invoices}" as="invoice">'
                    . '{invoice.id}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'invoices' => [
                    'invoice1' => $invoice1,
                    'invoice2' => $invoice2,
                    'invoice3' => $invoice3,
                ],
            ],
            'groupKey: 345513600' . chr(10)
            . '1' . chr(10)
            . '3' . chr(10)
            . 'groupKey: 1278201600' . chr(10)
            . '2' . chr(10),
        ];

        $photoshop = new \stdClass();
        $photoshop->name = 'Adobe Photoshop';
        $photoshop->license = 'commercial';
        $typo3 = new \stdClass();
        $typo3->name = 'TYPO3';
        $typo3->license = 'GPL';
        $office = new \stdClass();
        $office->name = 'Microsoft Office';
        $office->license = 'commercial';
        yield 'group by not existing key creates one group' => [
            '<f:groupedFor each="{products}" as="products" groupBy="notExists" groupKey="myGroupKey">'
                . 'groupKey: {myGroupKey}' . chr(10)
                . '<f:for each="{products}" as="product" key="productKey">'
                    . '{productKey}: {product.license}' . chr(10)
                . '</f:for>'
            . '</f:groupedFor>',
            [
                'products' => new \ArrayIterator([
                    'photoshop' => $photoshop,
                    'typo3' => $typo3,
                    'office' => $office,
                ]),
            ],
            'groupKey: ' . chr(10)
            . 'photoshop: commercial' . chr(10)
            . 'typo3: GPL' . chr(10)
            . 'office: commercial' . chr(10),
        ];

        yield 'variables are restored correctly' => [
            '<f:groupedFor each="{allProducts}" as="groupedProducts" groupBy="license" groupKey="myGroupKey"></f:groupedFor>{groupedProducts} {myGroupKey}',
            [
                'allProducts' => [
                    'photoshop' => ['name' => 'Adobe Photoshop', 'license' => 'commercial'],
                    'typo3' => ['name' => 'TYPO3', 'license' => 'GPL'],
                    'office' => ['name' => 'Microsoft Office', 'license' => 'commercial'],
                    'drupal' => ['name' => 'Drupal', 'license' => 'GPL'],
                    'wordpress' => ['name' => 'Wordpress', 'license' => 'GPL'],
                ],
                'groupedProducts' => '[initial groupedProducts]',
                'myGroupKey' => '[initial myGroupKey]',
            ],
            '[initial groupedProducts] [initial myGroupKey]',
        ];

        yield 'variables set inside can be used outside' => [
            '<f:groupedFor each="{allProducts}" as="groupedProducts" groupBy="license"><f:variable name="groupedProducts" value="overwritten" /></f:groupedFor>{groupedProducts}',
            [
                'allProducts' => [
                    'photoshop' => ['name' => 'Adobe Photoshop', 'license' => 'commercial'],
                    'typo3' => ['name' => 'TYPO3', 'license' => 'GPL'],
                    'office' => ['name' => 'Microsoft Office', 'license' => 'commercial'],
                    'drupal' => ['name' => 'Drupal', 'license' => 'GPL'],
                    'wordpress' => ['name' => 'Wordpress', 'license' => 'GPL'],
                ],
                'groupedProducts' => '[initial groupedProducts]',
            ],
            'overwritten',
        ];
    }

    #[DataProvider('renderDataProvider')]
    #[Test]
    public function render(string $template, array $variables, string $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);
        self::assertSame($expected, $view->render());
    }
}
