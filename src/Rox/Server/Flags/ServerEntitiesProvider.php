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
    function createString($defaultValue, array $variations)
    {
        return new RoxString($defaultValue, $variations);
    }

    /**
     * @inheritDoc
     */
    function createInt($defaultValue, array $variations)
    {
        return new RoxInt($defaultValue, $variations);
    }

    /**
     * @inheritDoc
     */
    function createDouble($defaultValue, array $variations)
    {
        return new RoxDouble($defaultValue, $variations);
    }
}
