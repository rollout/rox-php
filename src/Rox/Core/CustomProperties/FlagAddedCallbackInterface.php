<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Repositories\FlagRepositoryInterface;

interface FlagAddedCallbackInterface
{
    /**
     * @param FlagRepositoryInterface $repository
     * @param FlagAddedCallbackArgs $args
     * @return void
     */
    function onFlagAdded($repository, $args);
}