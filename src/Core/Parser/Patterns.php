<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class Patterns
 */
abstract class Patterns
{

    const NAMESPACEPREFIX = 'http://typo3.org/ns/';
    const NAMESPACESUFFIX = '/ViewHelpers';

    /**
     * This regular expression splits the input string at all dynamic tags, AND
     * on all <![CDATA[...]]> sections.
     */
    static public $SPLIT_PATTERN_TEMPLATE_DYNAMICTAGS = '/
		(
			(?: <\/?                                      # Start dynamic tags
					(?:(?:[a-zA-Z0-9\\.]*):[a-zA-Z0-9\\.]+)  # A tag consists of the namespace prefix and word characters
					(?:                                   # Begin tag arguments
						\s*[a-zA-Z0-9:-]+                 # Argument Keys
						\s*
						=                                 # =
						\s*
						(?>                               # either... If we have found an argument, we will not back-track (That does the Atomic Bracket)
							"(?:\\\"|[^"])*"              # a double-quoted string
							|\'(?:\\\\\'|[^\'])*\'        # or a single quoted string
						)\s*                              #
					)*                                    # Tag arguments can be replaced many times.
				\s*
				\/?>                                      # Closing tag
			)
			|(?:                                          # Start match CDATA section
				<!\[CDATA\[.*?\]\]>
			)
		)/xs';

    /**
     * This regular expression scans if the input string is a ViewHelper tag
     */
    static public $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG = '/
		^<                                                # A Tag begins with <
		(?P<NamespaceIdentifier>[a-zA-Z0-9\\.]*):         # Then comes the Namespace prefix followed by a :
		(?P<MethodIdentifier>                             # Now comes the Name of the ViewHelper
			[a-zA-Z0-9\\.]+
		)
		(?P<Attributes>                                   # Begin Tag Attributes
			(?:                                           # A tag might have multiple attributes
				\s*
				[a-zA-Z0-9:-]+                            # The attribute name
				\s*
				=                                         # =
				\s*
				(?>                                       # either... # If we have found an argument, we will not back-track (That does the Atomic Bracket)
					"(?:\\\"|[^"])*"                      # a double-quoted string
					|\'(?:\\\\\'|[^\'])*\'                # or a single quoted string
				)                                         #
				\s*
			)*
		)                                                 # End Tag Attributes
		\s*
		(?P<Selfclosing>\/?)                              # A tag might be selfclosing
		>$/x';

    /**
     * This regular expression scans if the input string is a closing ViewHelper
     * tag.
     */
    static public $SCAN_PATTERN_TEMPLATE_CLOSINGVIEWHELPERTAG =
        '/^<\/(?P<NamespaceIdentifier>[a-zA-Z0-9\\.]*):(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)\s*>$/';

    /**
     * Pattern which splits the shorthand syntax into different tokens. The
     * "shorthand syntax" is everything like {...}
     */
    static public $SPLIT_PATTERN_SHORTHANDSYNTAX = '/
		(
			{                                 # Start of shorthand syntax
				(?:                           # Shorthand syntax is either composed of...
					[^\\s\\}\\{]             # Anything not whitespace or curly braces
					|"(?:\\\"|[^"])*"         # Double-quoted strings
					|\'(?:\\\\\'|[^\'])*\'    # Single-quoted strings
					|(?R)                     # Other shorthand syntaxes inside, albeit not in a quoted string
					|\s+                      # Spaces
				)+
			}                                 # End of shorthand syntax
		)/x';

    /**
     * Pattern which detects the object accessor syntax:
     * {object.some.value}, additionally it detects ViewHelpers like
     * {f:for(param1:bla)} and chaining like
     * {object.some.value -> f:bla.blubb() -> f:bla.blubb2()}
     *
     * THIS IS ALMOST THE SAME AS IN $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
     */
    static public $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS = '/
		^{                                                  # Start of shorthand syntax
			                                                # A shorthand syntax is either...
			(?P<Object>[a-zA-Z0-9_\-\.\{\}]*)                 # ... an object accessor
			\s*(?P<Delimiter>(?:->|\|)?)\s*

			(?P<ViewHelper>                                 # ... a ViewHelper
				[a-zA-Z0-9\\.]+                             # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
				:
				[a-zA-Z0-9\\.]+                             # Method Identifier (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
				\(                                          # Opening parameter brackets of ViewHelper
					(?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
						(?:
							\s*[a-zA-Z0-9\-_]+              # The keys of the array
							\s*[:=]\s*                      # Key|Value delimiter : or =
							(?:                             # Possible value options:
								"(?:\\\"|[^"])*"            # Double qouoted string
								|\'(?:\\\\\'|[^\'])*\'      # Single quoted string
								|[a-zA-Z0-9\-_.]+           # variable identifiers
								|{(?P>ViewHelperArguments)} # Another sub-array
							)                               # END possible value options
							\s*,?                           # There might be a , to seperate different parts of the array
						)*                                  # The above cycle is repeated for all array elements
					)                                       # End ViewHelper Arguments submatch
				\)                                          # Closing parameter brackets of ViewHelper
			)?
			(?P<AdditionalViewHelpers>                      # There can be more than one ViewHelper chained, by adding more -> and the ViewHelper (recursively)
				(?:
					\s*(?P>Delimiter)\s*
					(?P>ViewHelper)
				)*
			)
		}$/x';

    /**
     * THIS IS ALMOST THE SAME AS $SCAN_PATTERN_SHORTHANDSYNTAX_OBJECTACCESSORS
     */
    static public $SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER = '/

		(?P<NamespaceIdentifier>[a-zA-Z0-9\\.]+)    # Namespace prefix of ViewHelper (as in $SCAN_PATTERN_TEMPLATE_VIEWHELPERTAG)
		:
		(?P<MethodIdentifier>[a-zA-Z0-9\\.]+)
		\(                                          # Opening parameter brackets of ViewHelper
			(?P<ViewHelperArguments>                # Start submatch for ViewHelper arguments. This is taken from $SCAN_PATTERN_SHORTHANDSYNTAX_ARRAYS
				(?:
					\s*[a-zA-Z0-9\-_]+              # The keys of the array
					\s*[:=]\s*                      # Key|Value delimiter : or =
					(?:                             # Possible value options:
						"(?:\\\"|[^"])*"            # Double qouoted string
						|\'(?:\\\\\'|[^\'])*\'      # Single quoted string
						|[a-zA-Z0-9\-_.]+           # variable identifiers
						|{(?P>ViewHelperArguments)} # Another sub-array
					)                               # END possible value options
					\s*,?                           # There might be a , to seperate different parts of the array
				)*                                  # The above cycle is repeated for all array elements
			)                                       # End ViewHelper Arguments submatch
		\)                                          # Closing parameter brackets of ViewHelper
		/x';

}
