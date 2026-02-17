<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Definition\Annotation;

/**
 * Annotations allow attaching arbitrary information to ViewHelper/Component
 * or argument definitions.
 *
 * @internal
 */
interface AnnotationInterface
{
    /**
     * Generates PHP code for the template cache that represents the
     * Annotation object. Depending on the future of TemplateCompiler,
     * this might no longer be necessary later.
     */
    public function compile(): string;
}
