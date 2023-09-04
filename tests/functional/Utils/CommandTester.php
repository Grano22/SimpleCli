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

    public static function run(string $scriptPath, string $appVariant): self
    {
        putenv("RUNNER_APP_VARIANT=$appVariant");
        exec($scriptPath, $output, $returnCode);

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
}