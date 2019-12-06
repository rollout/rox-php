<?php

namespace Rox\Server\Flags;

use Rox\Core\Entities\EntitiesProviderInterface;

class ServerEntitiesProvider implements EntitiesProviderInterface
{
    /**
     * @inheritDoc
     */
    function createFlag($defaultValue)
    {
        return new RoxFlag($defaultValue);
    }

    /**
     * @inheritDoc
     */
    function createVariant($defaultValue, array $options)
    {
        return new RoxVariant($defaultValue, $options);
    }
}
