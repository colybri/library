<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Queue;

use Colybri\Library\Domain\Messaging\DomainEventSubscriber;

final class RabbitMqQueueNameFormatter
{
    public static function format(DomainEventSubscriber $subscriber): string
    {
        $subscriberClassPaths = explode('\\', get_class($subscriber));

        $queueNameParts = [
            $subscriberClassPaths[0],
            $subscriberClassPaths[1],
            $subscriberClassPaths[4],
            end($subscriberClassPaths)
        ];

        return implode('.', array_map(function(string $text){
            return  self::toSnakeCase($text);
        }, $queueNameParts));
    }

    public static function shortFormat(DomainEventSubscriber $subscriber): string
    {
        $subscriberClassPaths = explode('\\', get_class($subscriber));

        $subscriberCamelCaseName = (string) end($subscriberClassPaths);

        return self::toSnakeCase($subscriberCamelCaseName);
    }
    public static function formatRetry(DomainEventSubscriber $subscriber): string
    {
        $queueName = self::format($subscriber);

        return "retry.$queueName";
    }

    private static function toSnakeCase(string $text): string
    {
        return ctype_lower($text) ? $text : strtolower(preg_replace('/([^A-Z\s])([A-Z])/', "$1_$2", $text));
    }

    public static function formatDeadLetter(DomainEventSubscriber $subscriber): string
    {
        $queueName = self::format($subscriber);

        return "dead_letter.$queueName";
    }
}