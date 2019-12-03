<?php

namespace Rox\Core\Security;

class SignatureVerifier implements SignatureVerifierInterface
{
    /**
     * @param string $data
     * @param string $signatureBase64
     * @return bool
     */
    function verify($data, $signatureBase64)
    {
        return true;
    }
}
