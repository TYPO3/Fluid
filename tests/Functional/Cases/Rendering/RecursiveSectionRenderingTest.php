<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Escaping;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class RecursiveSectionRendering
 */
class RecursiveSectionRenderingTest extends BaseFunctionalTestCase
{

    /**
     * Variables array constructed to expect exactly three
     * recursive renderings followed by a single rendering.
     *
     * @var array
     */
    protected $variables = [
        'settings' => [
            'test' => '<strong>Bla</strong>'
        ],
        'items' => [
            [
                'id' => 1,
                'items' => [
                    [
                        'id' => 2,
                        'items' => [
                            [
                                'id' => 3,
                                'items' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => 4
            ]
        ]
    ];

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        return [
            'Recursive section rendering clones variable storage and restores after loop ends' => [
                file_get_contents(__DIR__ . '/../../Fixtures/Templates/RecursiveSectionRendering.html'),
                $this->variables,
                ['Item: 1.', 'Item: 2.', 'Item: 3.', 'Item: 4.'],
                [],
            ],
        ];
    }
}
