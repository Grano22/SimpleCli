<?php

declare(strict_types=1);

use Grano22\SimpleCli\App\SimpleCliApp;

require_once __DIR__ . "/../../../vendor/autoload.php";

$testAppVariant = getenv('RUNNER_APP_VARIANT');

if (!$testAppVariant) {
    echo "Need to define RUNNER_APP_VARIANT";
    exit(1);
}

/** @var SimpleCliApp $appVariant */
$appVariant = (require_once "$testAppVariant");

$appVariant->autoExecuteCommand();