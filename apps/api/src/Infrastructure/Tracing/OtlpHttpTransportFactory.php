<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Tracing;

use GuzzleHttp\Client;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

/**
 * OTLP/HTTP transport that trusts the CA in OTEL_EXPORTER_OTLP_CERTIFICATE.
 *
 * The released exporter (open-telemetry/exporter-otlp 1.4) and SDK (1.15)
 * ignore that variable, so the APM Server certificate, signed by the local CA
 * of the Elastic stack, was rejected (cURL error 60). Registered in place of
 * the exporter's own factory by config/opentelemetry.php. Remove it once a release of
 * both packages passes the certificate to the HTTP client.
 */
final class OtlpHttpTransportFactory implements TransportFactoryInterface
{
    private const string NO_COMPRESSION = 'none';

    /**
     * @param array<string, string|string[]> $headers
     * @param string|string[]|null           $compression
     *
     * @return TransportInterface<string>
     */
    #[\Override]
    public function create(
        string $endpoint,
        string $contentType,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null,
    ): TransportInterface {
        if (self::NO_COMPRESSION === $compression) {
            $compression = null;
        }

        $cacert ??= self::configuredCertificate();
        $options = ['timeout' => $timeout];
        if (null !== $cacert) {
            $options['verify'] = $cacert;
        }

        return (new PsrTransportFactory(new Client($options)))
            ->create($endpoint, $contentType, $headers, $compression, $timeout, $retryDelay, $maxRetries);
    }

    private static function configuredCertificate(): ?string
    {
        foreach ([Variables::OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE, Variables::OTEL_EXPORTER_OTLP_CERTIFICATE] as $name) {
            if (Configuration::has($name)) {
                return Configuration::getString($name);
            }
        }

        return null;
    }
}
