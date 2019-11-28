<?php

namespace Rox\Core\Client;

interface SdkSettingsInterface
{
    /**
     * @return string
     */
    function getApiKey();

    /**
     * @return string
     */
    function getDevModeSecret();
}
