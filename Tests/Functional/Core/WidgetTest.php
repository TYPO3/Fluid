<?php
namespace TYPO3\Fluid\Tests\Functional\Core;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Mvc\Routing\Route;

/**
 * Testcase for the widget mechanism
 */
class WidgetTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * Additional setup: Routes
	 */
	public function setUp() {
		parent::setUp();

		$route = new Route();
		$route->setName('WidgetTest');
		$route->setUriPattern('test/widget/{@controller}(/{@action})');
		$route->setDefaults(array(
			'@package' => 'TYPO3.Fluid',
			'@subpackage' => 'Tests\Functional\Core\Fixtures',
			'@action' => 'index',
			'@format' =>'html'
		));
		$route->setAppendExceedingArguments(TRUE);
		$this->router->addRoute($route);
	}

	/**
	 * This sends a request to the helper controller (AjaxTestController) which includes
	 * the AJAX widget in its template. The indexAction renders that template which
	 * in turn lets the "someAjax" widget call the indexAction of its own controller
	 * (SomeAjaxController).
	 *
	 * @test
	 */
	public function ifIncludedInATemplateTheWidgetReturnsResultOfItsOwnIndexAction() {
		$response = $this->browser->request('http://localhost/test/widget/ajaxtest');
		list($confirmation,) = explode(chr(10), $response->getContent());
		$this->assertSame('SomeAjaxController::indexAction()', $confirmation);
	}

	/**
	 * This sends a request to the helper controller (AjaxTestController) which includes
	 * the AJAX widget in its template. The second line of the output created by the
	 * indexAction() of the "someAjax" widget contains a URI which allows for directly
	 * sending a request (from outside) to the widget, calling the ajaxAction().
	 *
	 * We send a request to this URI and check if the AJAX widget was really invoked.
	 *
	 * @test
	 */
	public function theGeneratedUriLeadsToASpecificActionOfTheAjaxController() {
		$response = $this->browser->request('http://localhost/test/widget/ajaxtest');
		list(,$ajaxWidgetUri) = explode(chr(10), $response->getContent());

		$response = $this->browser->request('http://localhost/' . $ajaxWidgetUri);
		$this->assertSame('SomeAjaxController::ajaxAction("value1", "value2")', $response->getContent());
	}

}
?>