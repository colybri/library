<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging\Message\ValueObject;

class MessageName
{
    public static function generate(
        VendorValueObject $company,
        ServiceValueObject $service,
        string $version,
        string $type,
        string $resource,
        string $name
    ): string {
        return \implode(
            '.',
            [
                $company->name(),
                $service->name(),
                $version,
                $type,
                $resource,
                $name,
            ],
        );
    }
}