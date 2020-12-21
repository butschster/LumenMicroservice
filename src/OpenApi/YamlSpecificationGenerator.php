<?php

namespace Butschster\Exchanger\OpenApi;

use Butschster\Exchanger\OpenApi\Annotation\Component\SchemaFactory;
use Butschster\Exchanger\Payloads\Error;
use Butschster\Exchanger\Payloads\Request;
use Butschster\Exchanger\Payloads\Response;
use OpenApi\Annotations\OpenApi;
use OpenApi\Serializer;
use Butschster\Exchanger\Contracts\Exchange\Point;
use Butschster\Exchanger\Contracts\ExchangeManager;
use Butschster\Exchanger\Exchange\Point\Parser;

class YamlSpecificationGenerator
{
    private Parser $parser;
    private ExchangeManager $exchangeManager;
    private SchemaFactory $schemasFactory;
    private Serializer $serializer;

    public function __construct(
        Parser $parser,
        Serializer $serializer,
        ExchangeManager $exchangeManager,
        SchemaFactory $schemasFactory
    )
    {
        $this->parser = $parser;
        $this->exchangeManager = $exchangeManager;
        $this->schemasFactory = $schemasFactory;
        $this->serializer = $serializer;
    }

    /**
     * Generate specification for given exchange point
     *
     * @param Point $point
     * @return string
     * @throws \Exception
     */
    public function generate(Point $point): string
    {
        $information = $this->parser->parse($point);

        $specification = new Specification(
            $this->exchangeManager->getVersion(),
            $point->getName()
        );

        $specification->appendSchemas(
            $this->schemasFactory->createFromArray([
                Request\Headers::class => 'RequestHeaders',
                Response\Headers::class => 'ResponseHeaders',
                Error::class => 'Error'
            ])
        );

        foreach ($information->getRoutes() as $route) {
            $specification->appendPath($route);
        }

        return $this->serializer->deserialize(
                json_encode($specification),
                OpenApi::class
            )
            ->toYaml();
    }
}
