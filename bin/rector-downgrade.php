<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) 2023 CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;

return static function (RectorConfig $config): void {
    $targetPhpVersionId = (int) getenv('TARGET_PHP_VERSION_ID');

    $config->paths([__DIR__ . '/../src']);
    $config->phpVersion($targetPhpVersionId);
    $config->disableParallel();

    if ($targetPhpVersionId < 80100) {
        $config->rule(DowngradePureIntersectionTypeRector::class);
        $config->rule(DowngradeReadonlyPropertyRector::class);
    }

    if ($targetPhpVersionId < 80000) {
        $config->rule(DowngradeNonCapturingCatchesRector::class);
        $config->rule(DowngradePropertyPromotionRector::class);
        $config->rule(DowngradeTrailingCommasInParamUseRector::class);
        $config->rule(DowngradeMixedTypeDeclarationRector::class);
        $config->rule(DowngradeUnionTypeDeclarationRector::class);
        $config->rule(DowngradeUnionTypeTypedPropertyRector::class);
    }
};
