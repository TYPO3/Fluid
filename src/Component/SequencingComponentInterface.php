<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Sequencer;

/**
 * Sequencing Component Interface
 *
 * Special type of component which, when used, takes over the
 * job of sequencing the Splitter's output and allows it to
 * change the contexts and tokens Fluid will detect, as well
 * as changing what happens when a token is encountered.
 *
 * Receives the Sequencer instance, which holds public properties
 * containing the RenderingContext, Splitter instance and context
 * collection used by the parser - and receives the namespace and
 * method which resolved to the component, as strings.
 *
 * Is only triggered for an opening-and-not-self-closed tag, since
 * affecting parsing only makes sense in tags with content.
 *
 * The purpose is to allow the Component to switch to a limited
 * context that only detects tag mode and disables all or some of
 * the tokens that are normally parsed as Fluid, treating them
 * instead as plain text.
 *
 * Use cases in Fluid:
 *
 * - f:description uses this to disable all parsing inside such tags.
 * - f:example does the same
 * - f:comment as well
 *
 * These protect contents of the tags from ever being parsed as
 * Fluid, making the component only end once a closing tag is
 * encountered at the same level (so recursive instances are not
 * detected and parsed).
 *
 * The sequence() method must return void. Internally it should use
 * a foreach($sequencer->sequence) loop with a switch() that reacts
 * to encountered symbols and "does something" with the captured
 * string content. For example, $this->addChild(new TextNode($captured));
 *
 * When the method returns, the topmost Component (this instance) is
 * popped from the parsing stack and closed. After this the Component
 * exists in the parsed tree along with any child Components you
 * added to it.
 *
 * The method should return precisely when encountering a closing
 * tag matching namespace and method. It may choose to ignore nested
 * instances by maintaining a count of "components to ignore" and
 * increasing this count for every *opening* tag matching namespace
 * and method, and decreasing it for every matching closing tag,
 * only returning if the counter is zero.
 */
interface SequencingComponentInterface extends ComponentInterface
{
    public function sequence(Sequencer $sequencer, ?string $namespace, string $method): void;
}
