--TEST--
fluid help
--ARGS--
help
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--

----------------------------------------------------------------------------------------------
				TYPO3 Fluid CLI: Help text
----------------------------------------------------------------------------------------------

Supported commands:

	bin/fluid help                 # Show this help screen
	bin/fluid run                  # Run fluid code, either interactively or file-based
	bin/fluid schema               # Generate xsd schema files based on all available ViewHelper classes
	bin/fluid warmup               # Warmup template cache
