<?php

namespace Butschster\Exchanger\OpenApi\Annotation;

use Butschster\Exchanger\Contracts\OpenApi\WithSchemas;
use Butschster\Exchanger\OpenApi\Annotation\Component\SchemaFactory;

class Responses implements WithSchemas
{
    private array $schemas = [];
    private SchemaFactory $factory;

    public function __construct(SchemaFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Generate responses for path
     * @param string|null $responsePayload
     * @return array[]
     */
    public function generate(?string $responsePayload = null): array
    {
        $responses = $this->getDefaultResponses();

        if ($responsePayload) {
            $this->schemas = array_merge($this->schemas, $this->factory->createFromPayload($responsePayload));

            $responses['200'] = [
                'description' => 'Success',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean',
                                    'description' => 'Response state'
                                ],
                                'headers' => [
                                    '$ref' => '#/components/schemas/ResponseHeaders',
                                ],
                                'payload' => [
                                    '$ref' => '#/components/schemas/' . class_basename($responsePayload)
                                ]
                            ]
                        ]
                    ]
                ],

            ];
        }

        return $responses;
    }

    protected function getDefaultResponses(): array
    {
        return [
            'default' => [
                'description' => 'Something went wrong',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error',
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }
}
