<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $containerConfigurator): void {
    // Contao
    $containerConfigurator->import(__DIR__ . '../../../../../contao/easy-coding-standard/config/contao.php');
    // Custom
    $containerConfigurator->import(__DIR__.'/set/header_comment_fixer.php');
};
