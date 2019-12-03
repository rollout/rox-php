<?php

namespace Rox\Core\Security;

interface SignatureVerifierInterface
{
    /**
     * @param string $data
     * @param string $signatureBase64
     * @return bool
     */
    function verify($data, $signatureBase64);
}
