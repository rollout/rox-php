<?php

namespace Rox\Core\Repositories;

use Rox\Core\Entities\RoxStringBase;

interface FlagRepositoryInterface
{
    /**
     * @param RoxStringBase $variant
     * @param string $name
     * @return void
     */
    function addFlag($variant, $name);

    /**
     * @param string $name
     * @return RoxStringBase|null
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
