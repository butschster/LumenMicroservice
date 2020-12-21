<?php

namespace Butschster\Exchanger\OpenApi;

use Butschster\Exchanger\Exchange\Point\Subject;
use Butschster\Exchanger\OpenApi\Annotation\Component\SchemaFactory;
use Butschster\Exchanger\OpenApi\Annotation\Path;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Specification implements Arrayable, JsonSerializable
{
    private string $openApiVersion = '3.0.0';
    private string $version;
    private string $title;
    private array $servers = [];
    private array $paths = [];
    private array $schemas = [];

    public function __construct(string $version, string $title)
    {
        $this->version = $version;
        $this->title = $title;
    }

    /**
     * Add server to specification
     * @param string $url
     */
    public function addServer(string $url): void
    {
        $this->servers[] = [
            'url' => $url
        ];
    }

    /**
     * Append new path to specification
     * @param Subject $subject
     */
    public function appendPath(Subject $subject): void
    {
        $path = new Path(new SchemaFactory());
        $this->paths = array_merge($this->paths, $path->generate($subject));

        $this->appendSchemas($path->getSchemas());
    }

    /**
     * Append schemas to specification
     * @param array $schemas
     */
    public function appendSchemas(array $schemas): void
    {
        $this->schemas = array_merge($this->schemas, $schemas);
    }

    /**
     * Convert specification to array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'openapi' => $this->openApiVersion,
            'info' => [
                'title' => $this->title,
                'version' => $this->version
            ],
            'servers' => $this->servers,
            'paths' => $this->paths,
            'components' => [
                'schemas' => $this->schemas
            ]
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
