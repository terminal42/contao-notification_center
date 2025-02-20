<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreErrorsOnPackage('symfony/translation', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('contao/newsletter-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreUnknownClasses([Symfony\Component\HttpKernel\UriSigner::class, Contao\ModulePassword::class])
;
