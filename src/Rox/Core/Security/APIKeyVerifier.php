<?php

namespace Rox\Core\Security;

class APIKeyVerifier implements APIKeyVerifierInterface
{
    /**
     * @param string $apiKey
     * @return bool
     */
    function verify($apiKey)
    {
        return true;
    }
}
