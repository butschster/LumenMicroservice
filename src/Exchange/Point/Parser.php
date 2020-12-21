<?php

namespace Butschster\Exchanger\Exchange\Point;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Butschster\Exchanger\Contracts\Exchange;
use Butschster\Exchanger\Exceptions\AnnotationTagNotFoundException;

/**
 * @internal
 */
class Parser
{
    public function parse(Exchange\Point $exchange): Information
    {
        return new Information(
            $this->findSubjects($exchange)
        );
    }

    private function findSubjects(Exchange\Point $exchange): Collection
    {
        $reflection = new ReflectionClass($exchange);

        return collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(function (ReflectionMethod $reflectionMethod) {
                $comment = $reflectionMethod->getDocComment();
                return !$reflectionMethod->isConstructor() && $comment !== false;
            })->map(function (ReflectionMethod $reflectionMethod) use ($exchange) {
                return $this->makeSubject($exchange, $reflectionMethod);
            })->flatten(1);
    }

    private function makeSubject(Exchange\Point $exchange, ReflectionMethod $reflectionMethod): array
    {
        $comment = $reflectionMethod->getDocComment();
        $docBlock = DocBlockFactory::createInstance()->create($comment);

        $subjects = $this->getTagsFromCommentByKey($docBlock, 'subject');
        if ($subjects->isEmpty()) {
            throw new AnnotationTagNotFoundException('subject');
        }

        $requestPayload = $this->getTagsFromCommentByKey($docBlock, 'requestPayload')->first();
        if ($requestPayload && !class_exists($requestPayload)) {
            throw new InvalidArgumentException(sprintf('Request payload [%s] is not found', $requestPayload));
        }

        $responsePayload = $this->getTagsFromCommentByKey($docBlock, 'responsePayload')->first();
        if ($responsePayload && !class_exists($responsePayload)) {
            throw new InvalidArgumentException(sprintf('Response payload [%s] is not found', $responsePayload));
        }

        return $subjects->map(function (string $subject) use ($reflectionMethod, $docBlock, $exchange, $requestPayload, $responsePayload) {
            return new Subject(
                $exchange,
                $reflectionMethod->getName(),
                $docBlock->getSummary(),
                $subject,
                $this->getParameters($reflectionMethod),
                $this->getMiddleware($docBlock),
                $this->getDisabledMiddleware($docBlock),
                $requestPayload,
                $responsePayload
            );
        })->all();
    }

    private function getMiddleware(DocBlock $docBlock): array
    {
        return $this->getTagsFromCommentByKey($docBlock, 'middleware')->toArray();
    }

    private function getDisabledMiddleware(DocBlock $docBlock): array
    {
        return $this->getTagsFromCommentByKey($docBlock, 'disableMiddleware')->toArray();
    }

    /**
     * Get tags from php doc block by key
     * @param DocBlock $docBlock
     * @param string $key
     * @return Collection
     */
    private function getTagsFromCommentByKey(DocBlock $docBlock, string $key): Collection
    {
        $tags = $docBlock->getTagsByName($key);

        return collect($tags)->map(function ($tag) {
            return (string)$tag->getDescription();
        });
    }

    private function makeParameter(ReflectionParameter $reflectionParameter): Argument
    {
        return new Argument(
            $reflectionParameter->getName(),
            $reflectionParameter->getClass()->getName()
        );
    }

    private function getParameters(ReflectionMethod $reflectionMethod): Collection
    {
        return collect($reflectionMethod->getParameters())
            ->map(function (ReflectionParameter $reflectionParameter) {
                return $this->makeParameter($reflectionParameter);
            });
    }
}
