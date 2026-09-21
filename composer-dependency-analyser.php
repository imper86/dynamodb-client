<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return new Configuration()
    // Required at runtime by Symfony's PhpDocExtractor, never referenced directly.
    ->ignoreErrorsOnPackage('phpdocumentor/reflection-docblock', [ErrorType::UNUSED_DEPENDENCY])
;
