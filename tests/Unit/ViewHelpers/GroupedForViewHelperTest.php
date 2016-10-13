<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
use TYPO3Fluid\Fluid\ViewHelpers\GroupedForViewHelper;

/**
 * Testcase for GroupedForViewHelperTest
 */
class GroupedForViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var \TYPO3Fluid\Fluid\ViewHelpers\GroupedForViewHelper
     */
    protected $viewHelper;

    public function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(GroupedForViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfObjectIsNull()
    {
        $this->viewHelper->setArguments(['each' => null, 'as' => 'foo', 'groupBy' => 'bar', 'groupKey' => null]);
        $this->assertEquals('', $this->viewHelper->initializeArgumentsAndRender());
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfObjectIsEmptyArray()
    {
        $this->viewHelper->setArguments(['each' => [], 'as' => 'foo', 'groupBy' => 'bar', 'groupKey' => null]);
        $this->assertEquals('', $this->viewHelper->initializeArgumentsAndRender());
    }

    /**
     * @test
     * @expectedException \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function renderThrowsExceptionWhenPassingObjectsToEachThatAreNotTraversable()
    {
        $object = new \stdClass();
        $this->viewHelper->setArguments(
            ['each' => $object, 'as' => 'innerVariable', 'groupBy' => 'someKey', 'groupKey' => null]
        );
        $this->viewHelper->render();
    }

    /**
     * @test
     */
    public function renderGroupsMultidimensionalArrayAndPreservesKeys()
    {
        $photoshop = ['name' => 'Adobe Photoshop', 'license' => 'commercial'];
        $typo3 = ['name' => 'TYPO3', 'license' => 'GPL'];
        $office = ['name' => 'Microsoft Office', 'license' => 'commercial'];
        $drupal = ['name' => 'Drupal', 'license' => 'GPL'];
        $wordpress = ['name' => 'Wordpress', 'license' => 'GPL'];

        $products = ['photoshop' => $photoshop, 'typo3' => $typo3, 'office' => $office, 'drupal' => $drupal, 'wordpress' => $wordpress];

        $this->viewHelper->setArguments(
            ['each' => $products, 'as' => 'products', 'groupBy' => 'license', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsMultidimensionalArrayObjectAndPreservesKeys()
    {
        $photoshop = ['name' => 'Adobe Photoshop', 'license' => 'commercial'];
        $typo3 = ['name' => 'TYPO3', 'license' => 'GPL'];
        $office = ['name' => 'Microsoft Office', 'license' => 'commercial'];
        $drupal = ['name' => 'Drupal', 'license' => 'GPL'];
        $wordpress = ['name' => 'Wordpress', 'license' => 'GPL'];

        $products = ['photoshop' => $photoshop, 'typo3' => $typo3, 'office' => $office, 'drupal' => $drupal, 'wordpress' => $wordpress];

        $this->viewHelper->setArguments(
            ['each' => $products, 'as' => 'products', 'groupBy' => 'license', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsArrayOfObjectsAndPreservesKeys()
    {
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

        $products = ['photoshop' => $photoshop, 'typo3' => $typo3, 'office' => $office, 'drupal' => $drupal, 'wordpress' => $wordpress];

        $this->viewHelper->setArguments(
            ['each' => $products, 'as' => 'products', 'groupBy' => 'license', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsIteratorOfObjectsAndPreservesKeys()
    {
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

        $products = new \ArrayIterator(
            ['photoshop' => $photoshop, 'typo3' => $typo3, 'office' => $office, 'drupal' => $drupal, 'wordpress' => $wordpress]
        );

        $this->viewHelper->setArguments(
            ['each' => $products, 'as' => 'products', 'groupBy' => 'license', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsMultidimensionalArrayByObjectKey()
    {
        $customer1 = new \stdClass();
        $customer1->name = 'Anton Abel';

        $customer2 = new \stdClass();
        $customer2->name = 'Balthasar Bux';

        $invoice1 = ['date' => new \DateTime('1980-12-13'), 'customer' => $customer1];
        $invoice2 = ['date' => new \DateTime('2010-07-01'), 'customer' => $customer1];
        $invoice3 = ['date' => new \DateTime('2010-07-04'), 'customer' => $customer2];

        $invoices = ['invoice1' => $invoice1, 'invoice2' => $invoice2, 'invoice3' => $invoice3];

        $this->viewHelper->setArguments(
            ['each' => $invoices, 'as' => 'invoices', 'groupBy' => 'customer', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsMultidimensionalArrayByPropertyPath()
    {
        $customer1 = new \stdClass();
        $customer1->name = 'Anton Abel';

        $customer2 = new \stdClass();
        $customer2->name = 'Balthasar Bux';

        $invoice1 = new \stdClass();
        $invoice1->customer = $customer1;

        $invoice2 = new \stdClass();
        $invoice2->customer = $customer1;

        $invoice3 = new \stdClass();
        $invoice3->customer = $customer2;

        $invoices = ['invoice1' => $invoice1, 'invoice2' => $invoice2, 'invoice3' => $invoice3];

        $this->viewHelper->setArguments(
            ['each' => $invoices, 'as' => 'invoices', 'groupBy' => 'customer.name', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsMultidimensionalObjectByObjectKey()
    {
        $customer1 = new \stdClass();
        $customer1->name = 'Anton Abel';

        $customer2 = new \stdClass();
        $customer2->name = 'Balthasar Bux';

        $invoice1 = new \stdClass();
        $invoice1->date = new \DateTime('1980-12-13');
        $invoice1->customer = $customer1;

        $invoice2 = new \stdClass();
        $invoice2->date = new \DateTime('2010-07-01');
        $invoice2->customer = $customer1;

        $invoice3 = new \stdClass();
        $invoice3->date = new \DateTime('2010-07-04');
        $invoice3->customer = $customer2;

        $invoices = ['invoice1' => $invoice1, 'invoice2' => $invoice2, 'invoice3' => $invoice3];

        $this->viewHelper->setArguments(
            ['each' => $invoices, 'as' => 'invoices', 'groupBy' => 'customer', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderGroupsMultidimensionalObjectByDateTimeObject()
    {
        $date1 = new \DateTime('2010-07-01');
        $date2 = new \DateTime('2010-07-04');

        $invoice1 = new \stdClass();
        $invoice1->date = $date1;
        $invoice1->id = 12340;

        $invoice2 = new \stdClass();
        $invoice2->date = $date1;
        $invoice2->id = 12341;

        $invoice3 = new \stdClass();
        $invoice3->date = $date2;
        $invoice3->id = 12342;

        $invoices = ['invoice1' => $invoice1, 'invoice2' => $invoice2, 'invoice3' => $invoice3];
        $this->viewHelper->setArguments(
            ['each' => $invoices, 'as' => 'invoices', 'groupBy' => 'date', 'groupKey' => 'myGroupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function groupingByAKeyThatDoesNotExistCreatesASingleGroup()
    {
        $photoshop = ['name' => 'Adobe Photoshop', 'license' => 'commercial'];
        $typo3 = ['name' => 'TYPO3', 'license' => 'GPL'];
        $office = ['name' => 'Microsoft Office', 'license' => 'commercial'];

        $products = ['photoshop' => $photoshop, 'typo3' => $typo3, 'office' => $office];

        $this->viewHelper->setArguments(
            ['each' => $products, 'as' => 'innerKey', 'groupBy' => 'NonExistingKey', 'groupKey' => 'groupKey']
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     * @expectedException \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function renderThrowsExceptionWhenPassingOneDimensionalArraysToEach()
    {
        $values = ['some', 'simple', 'array'];

        $this->viewHelper->setArguments(
            ['each' => $values, 'as' => 'innerVariable', 'groupBy' => 'someKey', 'groupKey' => null]
        );
        $this->viewHelper->initializeArgumentsAndRender();
    }
}
