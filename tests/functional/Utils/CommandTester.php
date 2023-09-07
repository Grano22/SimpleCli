<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Tests\Functional\Utils;

use PHPUnit\Framework\Assert;

class CommandTester extends Assert
{
    public function __construct(
        private int $lastReturnCode,
        private array $lastOutput
    ) {}

    public static function run(string $scriptPath, string $appVariant, array $argDefinitions = []): self
    {
        putenv("RUNNER_APP_VARIANT=$appVariant");
        exec($scriptPath, $output, $returnCode);

        if ($argDefinitions !== []) {
            putenv('RUNNER_APP_EXTENSIONS=' . json_encode($argDefinitions));
        }

        return new self($returnCode, $output);
    }

    public function assertReturnCodeEquals(int $expectedReturnCode): void
    {
        self::assertSame(
            $expectedReturnCode,
            $this->lastReturnCode,
            "Return code {$this->lastReturnCode} is different than expected $expectedReturnCode"
        );
    }

    public function assertOutputEquals(string $expectedOutput): void
    {
        self::assertSame(
            $expectedOutput,
            implode("\r\n", $this->lastOutput),
            "Output is different"
        );
    }

    private static function prepareCsvFromArray(array $arrayData): string
    {
        $rows = '';

        $fd = fopen('php://memory','rw');
        foreach($arrayData as $datum) {
            fputcsv($fd, $datum);
            rewind($fd);
            $rows .= trim(fgets($fd)) . PHP_EOL;
            rewind($fd);
        }
        fclose($fd);

        return $rows;
    }
}