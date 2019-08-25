<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Source reflecting a piece of Fluid source code,
 * contained within a template file.
 */
class FileSource extends Source
{
    public $source = '';
    public $filePathAndFilename = '';
    public $bytes = [];
    public $length = 0;

    public function __construct(string $filePathAndFilename)
    {
        $this->filePathAndFilename = $filePathAndFilename;
        parent::__construct(file_get_contents($filePathAndFilename));
    }
}
