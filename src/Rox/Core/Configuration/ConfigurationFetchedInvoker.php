<?php

namespace Rox\Core\Configuration;

class ConfigurationFetchedInvoker extends AbstractConfigurationFetchedInvoker
{
    protected final function internalInvoke(ConfigurationFetchedArgs $cfa)
    {
        $this->fireConfigurationFetched($cfa);
    }
}
