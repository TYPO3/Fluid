<?php
namespace TYPO3Fluid\Fluid\Tests\Profiling\Parser;

use NamelessCoder\NumerologPhpunit\StatisticalUnitTestTrait;
use NamelessCoder\PhpunitXhprof\ProfilingTrait;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class TemplateSourceParsingProfileStatisticsTest
 */
class TemplateSourceParsingProfileStatisticsTest extends UnitTestCase {

	use ProfilingTrait;
	use StatisticalUnitTestTrait;

	/**
	 * @param string $setName
	 * @param string $source
	 * @param array $variables
	 * @test
	 * @dataProvider getNodeCountProfilingData
	 */
	public function parsingCreatesExpectedOrLowerNodeCount($setName, $source, array $variables) {
		$parser = new TemplateParser();
		$closure = function() use ($parser, $source) {
			$parser->parse($source);
		};
		$results = $this->profileClosure($closure, array(
			'/.+Node::__construct$/'
		));
		$createdNodes = 0;
		foreach ($results as $counterData) {
			$createdNodes += $counterData['ct'];
		}
		$this->assertLessThanOrEqualToMinimum($setName, $createdNodes);
	}

	/**
	 * @return array
	 */
	public function getNodeCountProfilingData() {
		return array(
			array('singleObjectAccessor', '{a}', array('a' => 'foobar')),
			array('twoTextNodesAroundObjectAccessor', 'Some text with {a} inside', array('a' => 'foobar')),
			array('twoTextNodesAroundViewHelperWithAccessor', 'Text <f:count>{a}</f:count> around', array('a' => array(1, 2, 3))),
			array('viewHelperWithAccessorInline', '{a -> f:count()}', array('a' => array(1, 2, 3))),
			array('viewHelperWithAccessorTag', '<f:count>{variable}</f:count>', array('a' => array(1, 2, 3))),
			array('arrayNodeAsViewHelperArgument', '<f:alias map="{foo: a}">{foo}</f:alias>', array('a' => 'foobar')),
		);
	}

}
