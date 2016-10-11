<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmupResult;
use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class FluidCacheWarmupResultTest
 */
class FluidCacheWarmupResultTest extends UnitTestCase {

	/**
	 * @param array $results
	 * @param array $expected
	 * @dataProvider getCacheWarmupResultTestValues
	 * @test
	 */
	public function testMerge(array $results, array $expected) {
		$result1 = $this->getAccessibleMock(FluidCacheWarmupResult::class, array('dummy'));
		$result1->_set('results', array_pop($results));
		$result2 = $this->getAccessibleMock(FluidCacheWarmupResult::class, array('dummy'));
		$result2->_set('results', array_pop($results));
		$result1->merge($result2);
		$this->assertAttributeSame($expected, 'results', $result1);
	}

	/**
	 * @return array
	 */
	public function getCacheWarmupResultTestValues() {
		return array(
			array(array(array('foo' => 'bar'), array('baz' => 'oof')), array('baz' => 'oof', 'foo' => 'bar')),
			array(array(array('foo' => 'bar'), array('baz' => 'oof', 'foo' => 'baz')), array('baz' => 'oof', 'foo' => 'baz')),
		);
	}

	/**
	 * @test
	 */
	public function testGetResults() {
		$subject = $this->getAccessibleMock(FluidCacheWarmupResult::class, array('dummy'));
		$subject->_set('results', array('foo' => 'bar'));
		$this->assertAttributeEquals(array('foo' => 'bar'), 'results', $subject);
	}

	/**
	 * @param ParsedTemplateInterface $subject
	 * @param array $expected
	 * @dataProvider getAddTestValues
	 * @test
	 */
	public function testAdd(ParsedTemplateInterface $subject, array $expected) {
		$result = new FluidCacheWarmupResult();
		$result->add($subject, 'foobar');
		$this->assertAttributeEquals(array('foobar' => $expected), 'results', $result);
	}

	/**
	 * @return array
	 */
	public function getAddTestValues() {
		$subject1 = $this->getMockBuilder(
			ParsedTemplateInterface::class
		)->setMethods(
			array('isCompiled', 'isCompilable', 'hasLayout', 'getIdentifier')
		)->getMockForAbstractClass();
		$subject1->expects($this->exactly(2))->method('isCompiled')->willReturn(FALSE);
		$subject1->expects($this->once())->method('isCompilable')->willReturn(TRUE);
		$subject1->expects($this->once())->method('hasLayout')->willReturn(FALSE);
		$subject1->expects($this->once())->method('getIdentifier')->willReturn('subject1-identifier');
		$subject2 = $this->getMockBuilder(
			FailedCompilingState::class
		)->setMethods(
			array('isCompiled', 'isCompilable', 'hasLayout', 'getIdentifier', 'getFailureReason', 'getMitigations')
		)->getMockForAbstractClass();
		$subject2->expects($this->exactly(2))->method('isCompiled')->willReturn(TRUE);
		$subject2->expects($this->once())->method('isCompilable')->willReturn(TRUE);
		$subject2->expects($this->once())->method('hasLayout')->willReturn(TRUE);
		$subject2->expects($this->once())->method('getIdentifier')->willReturn('subject2-identifier');
		$subject2->expects($this->once())->method('getFailureReason')->willReturn('failure-reason');
		$subject2->expects($this->once())->method('getMitigations')->willReturn(array('m1', 'm2'));
		return array(
			array(
				$subject1,
				array(
					FluidCacheWarmupResult::RESULT_COMPILABLE => TRUE,
					FluidCacheWarmupResult::RESULT_COMPILED => FALSE,
					FluidCacheWarmupResult::RESULT_HASLAYOUT => FALSE,
					FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject1-identifier'
				)
			),
			array(
				$subject2,
				array(
					FluidCacheWarmupResult::RESULT_COMPILABLE => TRUE,
					FluidCacheWarmupResult::RESULT_COMPILED => TRUE,
					FluidCacheWarmupResult::RESULT_HASLAYOUT => TRUE,
					FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject2-identifier',
					FluidCacheWarmupResult::RESULT_FAILURE => 'failure-reason',
					FluidCacheWarmupResult::RESULT_MITIGATIONS => array('m1', 'm2')
				)
			),
		);
	}

}
