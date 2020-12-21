<?php

namespace Butschster\Exchanger\OpenApi\Annotation;

use Butschster\Exchanger\Contracts\OpenApi\WithSchemas;
use Butschster\Exchanger\OpenApi\Annotation\Component\SchemaFactory;

class RequestParameters implements WithSchemas
{
    private array $schemas = [];
    private SchemaFactory $factory;

    public function __construct(SchemaFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Generate request parameters for path
     * @param string|null $payloadClass
     * @return array[]
     */
    public function generate(?string $payloadClass = null): array
    {
        $parameters = [
            'name' => 'body',
            'in' => 'query',
            'required' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'headers' => [
                        '$ref' => '#/components/schemas/RequestHeaders'
                    ],
                ]
            ]
        ];

        if ($payloadClass) {
            $this->schemas = array_merge($this->schemas, $this->factory->createFromPayload($payloadClass));

            $parameters['schema']['properties']['payload'] = [
                '$ref' => '#/components/schemas/' . class_basename($payloadClass)
            ];
        };

        return [
            $parameters
        ];
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }
}
