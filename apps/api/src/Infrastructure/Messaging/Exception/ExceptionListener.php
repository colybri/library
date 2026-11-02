<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Exception;

use Assert\InvalidArgumentException;
use Assert\LazyAssertionException;
use Colybri\Library\Domain\Exception\ExistsException;
use Colybri\Library\Domain\Exception\LogicException;
use Colybri\Library\Domain\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class ExceptionListener
{
    private array $exceptions;

    public function __construct()
    {
        $this->exceptions = [
            //LazyAssertionException need to be first
            LazyAssertionException::class => Response::HTTP_BAD_REQUEST,
            NotFoundException::class => Response::HTTP_NOT_FOUND,
            LogicException::class => Response::HTTP_CONFLICT,
            ExistsException::class => Response::HTTP_CONFLICT,
            InvalidArgumentException::class => Response::HTTP_BAD_REQUEST
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {

        $exception = $event->getThrowable();

        if (true === is_a($exception, HandlerFailedException::class)) {
            $exception = $exception->getPrevious();
        }

        foreach ($this->exceptions as $key => $httpCode) {
            if (true === is_a($exception, $key)) {
                $response = new JsonResponse(
                    $this->serializeException($exception),
                    $httpCode
                );
                $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
                $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
                $event->setResponse($response);
                return;
            }
        }
    }

    private function serializeException(\Throwable $throwable): array
    {
        if ($throwable instanceof LazyAssertionException) {
            return $this->lazyException($throwable);
        }

        return [
            'error' => $throwable->getMessage()
        ];
    }

    private function lazyException(LazyAssertionException $throwable): array
    {
        return [
            'error' => 'The following assertions failed',
            'messages' => \array_map(
                static function (\InvalidArgumentException $arg) {
                    return $arg->getMessage();
                },
                $throwable->getErrorExceptions()
            )
        ];
    }
}
