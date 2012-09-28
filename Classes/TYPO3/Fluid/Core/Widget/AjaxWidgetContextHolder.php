<?php
namespace TYPO3\Fluid\Core\Widget;

/*
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * This object stores the WidgetContext for the currently active widgets
 * of the current user, to make sure the WidgetContext is available in
 * Widget AJAX requests.
 *
 * This class is only used internally by the widget framework.
 *
 * @Flow\Scope("session")
 */
class AjaxWidgetContextHolder {

	/**
	 * Counter which points to the next free Ajax Widget ID which
	 * can be used.
	 *
	 * @var integer
	 */
	protected $nextFreeAjaxWidgetId = 0;

	/**
	 * An array $ajaxWidgetIdentifier => $widgetContext
	 * which stores the widget context.
	 *
	 * @var array
	 */
	protected $widgetContexts = array();

	/**
	 * Get the widget context for the given $ajaxWidgetId.
	 *
	 * @param integer $ajaxWidgetId
	 * @return \TYPO3\Fluid\Core\Widget\WidgetContext
	 * @throws Exception\WidgetContextNotFoundException
	 */
	public function get($ajaxWidgetId) {
		$ajaxWidgetId = (int) $ajaxWidgetId;
		if (!isset($this->widgetContexts[$ajaxWidgetId])) {
			throw new \TYPO3\Fluid\Core\Widget\Exception\WidgetContextNotFoundException('No widget context was found for the Ajax Widget Identifier "' . $ajaxWidgetId . '". This only happens if AJAX URIs are called without including the widget on a page.', 1284793775);
		}
		return $this->widgetContexts[$ajaxWidgetId];
	}

	/**
	 * Stores the WidgetContext inside the Context, and sets the
	 * AjaxWidgetIdentifier inside the Widget Context correctly.
	 *
	 * @param \TYPO3\Fluid\Core\Widget\WidgetContext $widgetContext
	 * @return void
	 * @Flow\Session(autoStart=true)
	 */
	public function store(\TYPO3\Fluid\Core\Widget\WidgetContext $widgetContext) {
		$ajaxWidgetId = $this->nextFreeAjaxWidgetId++;
		$widgetContext->setAjaxWidgetIdentifier($ajaxWidgetId);
		$this->widgetContexts[$ajaxWidgetId] = $widgetContext;
	}
}

?>