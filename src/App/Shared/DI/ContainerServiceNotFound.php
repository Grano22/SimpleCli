<?php

declare(strict_types=1);

namespace AssertMutex\Runtime\Container;

use Grano22\SimpleCli\App\Shared\Lib\Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

/**
 * This exception is thrown when a non-existent service in container is requested.
 *
 * @author Adrian Błasiak <blasiak.adrian@gmail.com>
 */
class ServiceNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    private string $serviceId;

    public static function create(string $serviceId): self
    {
        return new self($serviceId);
    }

    private function __construct(string $serviceId)
    {
        $this->serviceId = $serviceId;

        parent::__construct(
            sprintf(
                'Service with id %s not found in container',
                $serviceId
            )
        );
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }
}
