<?php

namespace Rox\Core\Entities;

interface EntitiesProviderInterface
{
    /**
     * @param string $defaultValue
     * @return Flag
     */
    function createFlag($defaultValue);

    /**
     * @param string $defaultValue
     * @param array $options
     * @return Variant
     */
    function createVariant($defaultValue, array $options);
}
