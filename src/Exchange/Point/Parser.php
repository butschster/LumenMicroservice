<?php

namespace Butschster\Exchanger\Exchange\Point;

use Illuminate\Support\Collection;
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

    private function getTagsFromComment(string $comment, string $tag): Collection
    {
        $docBlock = DocBlockFactory::createInstance()->create($comment);
        $tags = $docBlock->getTagsByName($tag);

        if (count($tags) == 0) {
            throw new AnnotationTagNotFoundException($tag);
        }

        return collect($tags)->map(function (Generic $tag) {
            return $tag->getDescription();
        });
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
        $subjects = $this->getTagsFromComment($comment, 'subject');

        return $subjects->map(function (string $subject) use ($reflectionMethod, $comment, $exchange) {
            return new Subject(
                $exchange,
                $reflectionMethod->getName(),
                $exchange->getName() . '.' . $subject,
                $this->getParameters($reflectionMethod),
                $this->getMiddleware($comment),
                $this->getDisabledMiddleware($comment)
            );
        })->all();
    }

    private function getParameters(ReflectionMethod $reflectionMethod): Collection
    {
        return collect($reflectionMethod->getParameters())
            ->map(function (ReflectionParameter $reflectionParameter) {
                return $this->makeParameter($reflectionParameter);
            });
    }

    private function makeParameter(ReflectionParameter $reflectionParameter): Argument
    {
        return new Argument(
            $reflectionParameter->getName(),
            $reflectionParameter->getClass()->getName()
        );
    }

    private function getMiddleware(string $comment): array
    {
        $docBlock = DocBlockFactory::createInstance()->create($comment);
        $tags = $docBlock->getTagsByName('middleware');

        return collect($tags)
            ->map(function ($tag) {
                return (string)$tag->getDescription();
            })->toArray();
    }

    private function getDisabledMiddleware(string $comment): array
    {
        $docBlock = DocBlockFactory::createInstance()->create($comment);
        $tags = $docBlock->getTagsByName('disableMiddleware');

        return collect($tags)
            ->map(function ($tag) {
                return (string)$tag->getDescription();
            })->toArray();
    }
}
