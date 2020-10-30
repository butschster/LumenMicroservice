<?php

namespace Butschster\Exchanger\Jms\Handlers;

use Carbon\Carbon;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use Butschster\Exchanger\Contracts\Serializer;

class CarbonHandler implements Serializer\Handler
{
    /** @inheritDoc */
    public function serialize(Serializer $serializer, HandlerRegistryInterface $registry): void
    {
        $registry->registerHandler(
            GraphNavigatorInterface::DIRECTION_SERIALIZATION,
            Carbon::class,
            'json',
            function ($visitor, $date, array $type) {
                return $date->format(DATE_W3C);
            }
        );
    }

    /** @inheritDoc */
    public function deserialize(Serializer $serializer, HandlerRegistryInterface $registry): void
    {
        $registry->registerHandler(
            GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
            Carbon::class,
            'json',
            function ($visitor, $date, array $type) {
                if (!$date) {
                    return null;
                }
                try {
                    return Carbon::parse($date);
                } catch (\Exception $e) {
                }

                return null;
            }
        );
    }
}
