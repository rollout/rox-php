<?php

namespace Rox\Core\Client;

interface InternalFlagsInterface
{
    /**
     * @param string $flagName
     * @return bool
     */
    function isEnabled($flagName);

    /**
     * @param string $flagName
     * @return int|null
     */
    function getIntValue($flagName);
}
