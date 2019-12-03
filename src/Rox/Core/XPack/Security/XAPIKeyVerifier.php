<?php

namespace Rox\Core\XPack\Security;

use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Security\APIKeyVerifierInterface;

class XAPIKeyVerifier implements APIKeyVerifierInterface
{
    /**
     * @var SdkSettingsInterface $_sdkSettings
     */
    private $_sdkSettings;

    /**
     * XAPIKeyVerifier constructor.
     * @param SdkSettingsInterface $sdkSettings
     */
    public function __construct(SdkSettingsInterface $sdkSettings)
    {
        $this->_sdkSettings = $sdkSettings;
    }

    /**
     * @param string $apiKey
     * @return bool
     */
    function verify($apiKey)
    {
        return $apiKey === $this->_sdkSettings->getApiKey();
    }
}
