--TEST--
fluid --help
--ARGS--
--help
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--

----------------------------------------------------------------------------------------------
				TYPO3 Fluid CLI: Help text
----------------------------------------------------------------------------------------------

Supported parameters:

	--help                 # Shows usage examples
	--socket               # Path to socket (ignored unless running as socket server)
	--template             # A single template file to render
	--cacheDirectory       # Path to a directory used as cache for compiled Fluid templates
	--variables            # Variables (JSON string or JSON file) to use when rendering
	--controller           # Controller name to use when rendering in MVC mode
	--action               # Controller action when rendering in MVC mode
	--bootstrap            # A PHP file path or name of a PHP class (ClassName::functionToCall) which will bootstrap environment before rendering
	--templateRootPaths    # Template root paths, multiple paths can be passed separated by spaces
	--partialRootPaths     # Partial root paths, multiple paths can be passed separated by spaces
	--layoutRootPaths      # Layout root paths, multiple paths can be passed separated by spaces
	--renderingContext     # Class name of custom RenderingContext implementation to use when rendering

Use the CLI utility in the following modes:

Interactive mode:

    ./bin/fluid run
    (enter fluid template code, then enter key, then ctrl+d to send the input)

Or using STDIN:

    cat mytemplatefile.html | ./bin/fluid run

Or using parameters:

    ./bin/fluid run --template mytemplatefile.html

To specify multiple values, for example for the templateRootPaths argument:

    ./bin/fluid run --templateRootPaths /path/to/first/ /path/to/second/ "/path/with spaces/"

To specify variables, use any JSON source - string of JSON, local file or URI, or class
name of a PHP class implementing DataProviderInterface:

    ./bin/fluid run --variables /path/to/fluidvariables.json

    ./bin/fluid run --variables unix:/path/to/unixpipe

    ./bin/fluid run --variables http://offsite.com/variables.json

    ./bin/fluid run --variables `cat /path/to/fluidvariables.json`

    ./bin/fluid run --variables "TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider"

    ./bin/fluid run --variables "TYPO3Fluid\Fluid\Core\Variables\JSONVariableProvider:/path/to/file.json"

When specifying a VariableProvider class name it is possible to additionally add a
simple string value which gets passed to the VariableProvider through ->setSource()
upon instantiation. If working with custom VariableProviders, check the documentation
for each VariableProvider to know which source types are supported.

Should you require it you can pass the class name of a custom RenderingContext:

    ./bin/fluid run --renderingContext "My\Custom\RenderingContext"

Furthermore, should you require special bootstrapping of a framework, you can specify
an entry point containing a bootstrap (with or without output, does not matter) which
will be required/included as part of the initialisation.

    ./bin/fluid run --renderingContext "My\Custom\RenderingContext" --bootstrap /path/to/bootstrap.php

Or using a public, static function on a class which bootstraps:

    ./bin/fluid run --renderingContext "My\Custom\RenderingContext" --bootstrap MyBootstrapClass::bootstrapMethod

When passing a class-and-method bootstrap it is important that the method has no
required arguments and is possible to call as static method.

Be careful to use a bootstrapper which does not cause output if you intend to render templates.

A WebSocket mode is available. When starting the CLI utility in WebSocket mode,
very basic HTTP requests are rendered directly by listening on an IP:PORT combination:

    sudo ./bin/fluid run --socket 0.0.0.0:8080 --templateRootPaths /path/to/files/

Pointing your browser to http://localhost:8080 should then render the requested
file from the given path, defaulting to `index.html` when URI ends in `/`.

Note that when started this way, there is no DOCUMENT_ROOT except for the root
path you define as templateRootPaths. In this mode, the *FIRST* templateRootPath
gets used as if it were the DOCUMENT_ROOT.

Note also that this mode does not provide any $_SERVER or other variables of use
as would be done through for example Apache or Nginx.

An additional SocketServer mode is available. When started in SocketServer mode,
the CLI utility can be used as upstream (SCGI currently) in Nginx:

    sudo ./bin/fluid run --socket /var/run/fluid.sock

Example SCGI config for Nginx:

    location ~ \.html$ {
        scgi_pass unix:/var/run/fluid.sock;
        include scgi_params;
    }

End of help text for FLuid CLI.
