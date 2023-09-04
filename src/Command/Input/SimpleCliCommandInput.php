<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

class SimpleCliCommandInput {
    public function __construct(
        private SimpleCliArgumentsCollection $arguments,
        private SimpleCliOptionsCollection $options,
        private ?string $pipeData
    ) {}

    public function getArguments(): SimpleCliArgumentsCollection
    {
        return $this->arguments;
    }

    public function getOptions(): SimpleCliOptionsCollection
    {
        return $this->options;
    }

    public function getPipedData(bool $switched = false): string
    {
        if ($switched) {
            return $this->arguments->getPiped()->getValue();
        }

        return $this->pipeData;
    }
}
