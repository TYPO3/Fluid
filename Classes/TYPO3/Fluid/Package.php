<?php
namespace TYPO3\Fluid;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Fluid Package
 *
 */
class Package extends BasePackage {

	/**
	 * @var boolean
	 */
	protected $protected = TRUE;

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$context = $bootstrap->getContext();
		if (!$context->isProduction()) {
			$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function ($step) use ($bootstrap, $dispatcher) {
				if ($step->getIdentifier() === 'typo3.flow:systemfilemonitor') {
					$templateFileMonitor = \TYPO3\Flow\Monitor\FileMonitor::createFileMonitorAtBoot('Fluid_TemplateFiles', $bootstrap);
					$packageManager = $bootstrap->getEarlyInstance('TYPO3\Flow\Package\PackageManagerInterface');
					foreach ($packageManager->getActivePackages() as $packageKey => $package) {
						if ($packageManager->isPackageFrozen($packageKey)) {
							continue;
						}

						foreach (array('Templates', 'Partials', 'Layouts') as $path) {
							$templatesPath = $package->getResourcesPath() . 'Private/' . $path;

							if (is_dir($templatesPath)) {
								$templateFileMonitor->monitorDirectory($templatesPath);
							}
						}
					}

					$templateFileMonitor->detectChanges();
					$templateFileMonitor->shutdownObject();
				}
			});
		}

			// Use a closure to invoke the TemplateCompiler, since the object is not registered during compiletime
		$flushTemplates = function($identifier, $changedFiles) use ($bootstrap) {
			if ($identifier !== 'Flow_ClassFiles') {
				return;
			}

			$objectManager = $bootstrap->getObjectManager();
			if ($objectManager->isRegistered('TYPO3\Fluid\Core\Compiler\TemplateCompiler')) {
				$templateCompiler = $objectManager->get('TYPO3\Fluid\Core\Compiler\TemplateCompiler');
				$templateCompiler->flushTemplatesOnViewHelperChanges($changedFiles);
			}
		};
		$dispatcher->connect('TYPO3\Flow\Monitor\FileMonitor', 'filesHaveChanged', $flushTemplates);
	}
}
