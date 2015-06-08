<?php
namespace NamelessCoder\Fluid\Tests\Performance\Core\Variables;

use NamelessCoder\PHPUnitPerformance\PerformanceTestCase;
use NamelessCoder\Fluid\Core\Variables\VariableExtractor;

/**
 * Class VariableExtractorPerformanceTest
 */
class VariableExtractorPerformanceTest extends PerformanceTestCase {

	/**
	 * @return void
	 */
	public function warmUp() {
		new VariableExtractor();
	}

	/**
	 * @test
	 * @dataProvider getByPathPerformanceTestValues
	 * @param array $data
	 * @param string $path
	 * @param float $expected
	 * @param float $tolerance
	 */
	public function testGetByPathPerformance(array $data, $path, $expected, $tolerance) {
		$class = 'NamelessCoder\\Fluid\\Core\\Variables\\VariableExtractor';
		$this->assertPerformanceOfCallableWithinTolerance(array($class, 'extract'), array($path), $expected, $tolerance);
	}

	/**
	 * @return array
	 */
	public function getByPathPerformanceTestValues() {
		return array(
			array(array(), NULL, 0, 0.02)
		);
	}

}
