<?php

namespace Butschster\Exchanger;

use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Butschster\Exchanger\Contracts\Exchange;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Exchange\Config;
use Butschster\Exchanger\Exchange\Point\Information;
use Butschster\Exchanger\Exchange\Request;
use Butschster\Exchanger\Payloads\Payload;

class ExchangeManager implements Contracts\ExchangeManager
{
    private Exchange\Client $client;
    private Serializer $serializer;
    private LoggerInterface $logger;
    private Container $container;
    private string $version;
    private string $name;

    public function __construct(
        Container $container,
        Config $config,
        Exchange\Client $client,
        Serializer $serializer,
        LoggerInterface $logger
    )
    {
        $this->container = $container;
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->name = $config->name();
        $this->version = $config->version();
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritDoc */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /** @inheritDoc */
    public function register(Exchange\Point $exchange): void
    {
        $name = $exchange->getName();
        $this->logger->debug(sprintf(
            'Service [%s v.%s]: Registered exchange point [%s]',
            $this->getName(),
            $this->getVersion(),
            $name,
        ));

        $this->client->subscribe($exchange, function (Information $information) {
            foreach ($information->getRoutes() as $route) {
                $this->logger->debug(sprintf('Registered subject [%s]', $route->getSubject()));
            }
        });
    }

    /** @inheritDoc */
    public function request(string $subject, ?Exchange\Payload $payload = null): Exchange\Request
    {
        return new Request(
            $this->container->make(Exchange\PayloadFactory::class),
            $this->serializer,
            $this->client,
            $subject,
            $payload ?: new Payload()
        );
    }

    /** @inheritDoc */
    public function broadcast(string $subject, ?Exchange\Payload $payload = null): void
    {
        $this->request($subject, $payload)->call();
    }

    /**
     * This method uses for registering ConsoleLogger to send logs to the console.
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;

        $this->container->bind(LoggerInterface::class, function () {
            return $this->logger;
        });
    }
}
