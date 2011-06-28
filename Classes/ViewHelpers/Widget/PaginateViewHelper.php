<?php
namespace TYPO3\Fluid\ViewHelpers\Widget;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This ViewHelper renders a Pagination of objects.
 *
 * = Examples =
 *
 * <code title="simple configuration">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs" configuration="{itemsPerPage: 5}">
 *   // use {paginatedBlogs} as you used {blogs} before, most certainly inside
 *   // a <f:for> loop.
 * </f:widget.paginate>
 * </code>
 *
 * <code title="full configuration">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs" configuration="{itemsPerPage: 5, insertAbove: 1, insertBelow: 0, maximumNumberOfLinks: 10}">
 *   // This example will display at the maximum 10 links and tries to the settings
 *   // pagesBefore and pagesAfter into account to get the best result
 * </f:widget.paginate>
 * </code>
 * = Performance characteristics =
 *
 * In the above example, it looks like {blogs} contains all Blog objects, thus
 * you might wonder if all objects were fetched from the database.
 * However, the blogs are NOT fetched from the database until you actually use them,
 * so the paginate ViewHelper will adjust the query sent to the database and receive
 * only the small subset of objects.
 * So, there is no negative performance overhead in using the Paginate Widget.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class PaginateViewHelper extends \TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @inject
	 * @var TYPO3\Fluid\ViewHelpers\Widget\Controller\PaginateController
	 */
	protected $controller;

	/**
	 *
	 * @param \TYPO3\FLOW3\Persistence\QueryResultInterface $objects
	 * @param string $as
	 * @param array $configuration
	 * @return string
	 */
	public function render(\TYPO3\FLOW3\Persistence\QueryResultInterface $objects, $as, array $configuration = array('itemsPerPage' => 10, 'insertAbove' => FALSE, 'insertBelow' => TRUE, 'maximumNumberOfLinks' => 99)) {
		return $this->initiateSubRequest();
	}
}

?>