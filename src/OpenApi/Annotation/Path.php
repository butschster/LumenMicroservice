<?php

namespace Butschster\Exchanger\OpenApi\Annotation;

use Butschster\Exchanger\Contracts\OpenApi\WithSchemas;
use Butschster\Exchanger\Exchange\Point\Subject;
use Butschster\Exchanger\OpenApi\Annotation\Component\SchemaFactory;

class Path implements WithSchemas
{
    private array $schemas = [];
    private SchemaFactory $factory;

    public function __construct(SchemaFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Generate path information from subject
     * @param Subject $subject
     * @return \array[][]
     */
    public function generate(Subject $subject): array
    {
        $responses = new Responses($this->factory);
        $parameters = new RequestParameters($this->factory);

        $path = [
            '/' . $subject->getSubject() => [
                'get' => [
                    'summary' => $subject->getDescription(),
                    'parameters' => $parameters->generate($subject->getRequestPayload()),
                    'responses' => $responses->generate($subject->getResponsePayload()),
                ]
            ]
        ];

        $this->schemas = array_merge(
            $this->schemas,
            $parameters->getSchemas(),
            $responses->getSchemas()
        );

        return $path;
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }
}
