<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreUnknownClasses([
        'Contao\ModulePassword', // This class exists in Contao 4.13 but not in 5.3
    ])
    ->ignoreErrorsOnPackage('contao/newsletter-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD]) // This is an optional integration
    ->ignoreErrorsOnPackage('psr/log', [ErrorType::SHADOW_DEPENDENCY]) // Logging is optional
    ->ignoreErrorsOnPackage('terminal42/dcawizard', [ErrorType::UNUSED_DEPENDENCY]) // This is a widget used in the back end but not inside code
;