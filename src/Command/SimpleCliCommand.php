<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command;

use Closure;
use Grano22\SimpleCli\App\SimpleCliAppExecutionContext;
use Grano22\SimpleCli\Command\Input\Exception\CommandHandlerHasMissingReturnCode;
use Grano22\SimpleCli\Command\Input\SimpleCliArgumentsCollection;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOptionsCollection;

class SimpleCliCommand {
    public function __construct(
        private string $name,
        private Closure $executionLogic,
        private SimpleCliArgumentsCollection $definedArguments,
        private SimpleCliOptionsCollection $definedOptions
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefinedArguments(): SimpleCliArgumentsCollection
    {
        return $this->definedArguments;
    }

    public function getDefinedOptions(): SimpleCliOptionsCollection
    {
        return $this->definedOptions;
    }

    /**
     * @throws CommandHandlerHasMissingReturnCode
     */
    public function execute(?string $stdinPipedData, SimpleCliAppExecutionContext $context): int
    {
        $returnCode = $this->executionLogic->__invoke(
            new SimpleCliCommandInput(
                $this->definedArguments,
                $this->definedOptions,
                $stdinPipedData
            ),
            $context
        );

        if (!is_int($returnCode)) {
            throw CommandHandlerHasMissingReturnCode::createWithHandlerDetails($this->getName());
        }

        return $returnCode;
    }
}
