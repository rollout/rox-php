<?php

namespace Rox\Core\Security;

interface APIKeyVerifierInterface
{
    /**
     * @param string $apiKey
     * @return bool
     */
    function verify($apiKey);
}