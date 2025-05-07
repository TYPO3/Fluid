<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Compiler;

/**
 * Interface UncompilableTemplateInterface
 *
 * Implemented in compiled templates when the syntax tree could
 * not be fully compiled. Prevents continuous attempts to compile
 * the same template by allowing the template compiler to store
 * a class so the compiled identifier appears to exist, but return
 * nothing when asked to get() the identifier.
 *
 * The result is that the template parser will always parse the
 * original template.
 *
 * @internal This interface should be used for type-checks only.
 */
interface UncompilableTemplateInterface {}
