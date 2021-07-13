<?php

namespace Rox\Core\Entities;

interface EntitiesProviderInterface
{
    /**
     * @param bool $defaultValue
     * @return RoxStringBase
     */
    function createFlag($defaultValue);

    /**
     * @param string $defaultValue
     * @param array $variations
     * @return RoxStringBase
     */
    function createString($defaultValue, array $variations);

    /**
     * @param int $defaultValue
     * @param array $variations
     * @return RoxStringBase
     */
    function createInt($defaultValue, array $variations);

    /**
     * @param double $defaultValue
     * @param array $variations
     * @return RoxStringBase
     */
    function createDouble($defaultValue, array $variations);
}
