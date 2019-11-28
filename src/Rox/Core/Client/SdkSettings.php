<?php

namespace Rox\Core\Client;

class SdkSettings implements SdkSettingsInterface
{
    /**
     * @var string $_akiKey
     */
    private $_akiKey;

    /**
     * @var string $_devModeSecret
     */
    private $_devModeSecret;

    /**
     * SdkSettings constructor.
     * @param string $akiKey
     * @param string $devModeSecret
     */
    public function __construct($akiKey, $devModeSecret)
    {
        $this->_akiKey = $akiKey;
        $this->_devModeSecret = $devModeSecret;
    }

    /**
     * @return string
     */
    function getApiKey()
    {
        return $this->_akiKey;
    }

    /**
     * @return string
     */
    function getDevModeSecret()
    {
        return $this->_devModeSecret;
    }
}
