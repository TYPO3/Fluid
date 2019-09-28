<?php

if (!class_exists(TYPO3Fluid\Fluid\View\TemplateView::class)) {
    foreach ([__DIR__ . '/../../vendor/autoload.php', __DIR__ . '/../../../../autoload.php'] as $possibleAutoloadLocation) {
        if (file_exists($possibleAutoloadLocation)) {
            require_once $possibleAutoloadLocation;
        }
    }
}