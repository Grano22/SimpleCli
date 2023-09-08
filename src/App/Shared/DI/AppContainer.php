<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App\Shared\DI;

use AssertMutex\Runtime\Container\ServiceNotFoundException;
use Grano22\SimpleCli\App\Shared\Lib\Psr\Container\ContainerInterface;

class AppContainer implements ContainerInterface
{
    /** @var array<string, object> */
    private array $services = [];

    public static function create(array $initialServices = []): self
    {
        return new self($initialServices);
    }

    private function __construct(array $initialServices = [])
    {
        foreach ($initialServices as $initialServiceName => $serviceDefinition) {
            $this->services[$initialServiceName] = $serviceDefinition;
        }
    }

    public function get(string $id): object
    {
        return $this->services[$id] ?? ServiceNotFoundException::create($id);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }
}