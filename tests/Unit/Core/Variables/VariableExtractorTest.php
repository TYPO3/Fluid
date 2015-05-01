<?php
namespace TYPO3\Fluid\Tests\Unit\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Variables\VariableExtractor;
use TYPO3\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3\Fluid\Tests\UnitTestCase;

/**
 * Class VariableExtractorTest
 */
class VariableExtractorTest extends UnitTestCase {

	/**
	 * @param mixed $subject
	 * @param string $path
	 * @param mixed $expected
	 * @test
	 * @dataProvider getPathTestValues
	 */
	public function testGetByPath($subject, $path, $expected) {
		$result = VariableExtractor::extract($subject, $path);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getPathTestValues() {
		$namedUser = new UserWithoutToString('Foobar Name');
		$unnamedUser = new UserWithoutToString('');
		return array(
			array(NULL, '', NULL),
			array(array('foo' => 'bar'), 'foo', 'bar'),
			array(array('foo' => 'bar'), 'foo.invalid', NULL),
			array(array('user' => $namedUser), 'user.name', 'Foobar Name'),
			array(array('user' => $unnamedUser), 'user.name', ''),
			array(array('user' => $namedUser), 'user.named', TRUE),
			array(array('user' => $unnamedUser), 'user.named', FALSE),
			array(array('user' => $namedUser), 'user.invalid', NULL)
		);
	}

}
