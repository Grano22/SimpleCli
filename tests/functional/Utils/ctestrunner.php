<?php

declare(strict_types=1);

use Grano22\SimpleCli\App\SimpleCliApp;
use Grano22\SimpleCli\App\SimpleCliAppFactory;

require_once __DIR__ . "/../../../vendor/autoload.php";

$testAppVariant = getenv('RUNNER_APP_VARIANT');

if (!$testAppVariant) {
    echo "Need to define RUNNER_APP_VARIANT";
    exit(1);
}

/** @var SimpleCliAppFactory $appVariantProto */
$appVariantProto = (require_once "$testAppVariant") or die("Cannot locale app variant in path $testAppVariant");

$variantAppExtensions = getenv('RUNNER_APP_EXTENSIONS');

if ($variantAppExtensions) {
    $parsedAppExtensions = json_decode($variantAppExtensions, true);

    foreach ($parsedAppExtensions as $appExtension) {
        switch ($appExtension['action']) {
            case "createOption":
                $appVariantProto->withCommandsOption(
                    commandNames: $appExtension['commandsNames'],
                    name: $appExtension['name'],
                    options: $appExtension['options'] ?? 0b0
                );
                break;
            default: throw new RuntimeException("Undefined app extension action: {$appExtension['action']}");
        }
    }
}

$appVariant = $appVariantProto->build();

$appVariant->autoExecuteCommand();