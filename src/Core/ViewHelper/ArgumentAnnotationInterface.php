<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Argument annotations can be attached to an argument definition
 * to later trigger additional processing or special handling of the
 * argument. This base interface only marks an object as annotation
 * for type validation purposes.
 *
 * Note that argument annotations will be serialized before being
 * written to the template cache.
 *
 * @internal
 */
interface ArgumentAnnotationInterface {}
