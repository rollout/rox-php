<?php

namespace Rox\Core\Repositories;

use Rox\Core\Entities\Variant;

interface FlagRepositoryInterface
{
    /**
     * @param Variant $variant
     * @param string $name
     * @return void
     */
    function addFlag($variant, $name);

    /**
     * @param string $name
     * @return Variant|null
     */
    function getFlag($name);

    /**
     * @return array
     */
    function getAllFlags();

    /**
     * @param callable $callback
     * @return void
     */
    function addFlagAddedCallback($callback);
}
