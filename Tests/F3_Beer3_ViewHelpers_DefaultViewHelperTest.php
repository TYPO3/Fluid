<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3::ViewHelpers;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Beer3
 * @subpackage Tests
 * @version $Id:$
 */
/**
 * Testcase for DefaultViewHelper
 *
 * @package Beer3
 * @subpackage Tests
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class DefaultViewHelperTest extends F3::Testing::BaseTestCase {

	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->viewHelper = new F3::Beer3::ViewHelpers::DefaultViewHelper();
		$this->nodeMock = $this->getMock('F3::Beer3::NodeInterface');
	}
	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function forCallsRenderSubtreeCorrectly() {
		$this->nodeMock->expects($this->exactly(7))
		               ->method('renderChildNodes');
		$this->viewHelper->forMethod($this->nodeMock, array('each' => array(0,1,2,3,4,5,6), 'as' => 'a'));
	}
	
	/**
	 * @test
	 * @expectedException F3::Beer3::Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function forThrowsExceptionIfAsArgumentMissing() {
		$this->viewHelper->forMethod($this->nodeMock, array('each' => array()));
	}
}



?>