<?php
namespace TYPO3\Fluid\ViewHelpers\Widget\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class AutocompleteController extends \TYPO3\Fluid\Core\Widget\AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $configuration = array('limit' => 10);

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->configuration = \TYPO3\Flow\Utility\Arrays::arrayMergeRecursiveOverrule($this->configuration, $this->widgetConfiguration['configuration'], TRUE);
	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('id', $this->widgetConfiguration['for']);
	}

	/**
	 * @param string $term
	 * @return string
	 */
	public function autocompleteAction($term) {
		$searchProperty = $this->widgetConfiguration['searchProperty'];
		$query = clone $this->widgetConfiguration['objects']->getQuery();
		$constraint = $query->getConstraint();

		if ($constraint !== NULL) {
			$query->matching($query->logicalAnd(
				$constraint,
				$query->like($searchProperty, '%' . $term . '%', FALSE)
			));
		} else {
			$query->matching(
				$query->like($searchProperty, '%' . $term . '%', FALSE)
			);
		}
		if (isset($this->configuration['limit'])) {
			$query->setLimit((integer)$this->configuration['limit']);
		}

		$results = $query->execute();

		$output = array();
		$values = array();
		foreach ($results as $singleResult) {
			$val = \TYPO3\Flow\Reflection\ObjectAccess::getProperty($singleResult, $searchProperty);
			if (isset($values[$val])) continue;
			$values[$val] = TRUE;
			$output[] = array(
				'id' => $val,
				'label' => $val,
				'value' => $val
			);
		}
		return json_encode($output);
	}
}
?>