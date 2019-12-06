<?php

namespace Rox\Core\XPack\Configuration;

use Psr\Log\LoggerInterface;
use Rox\Core\Configuration\AbstractConfigurationFetchedInvoker;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Core;
use Rox\Core\Logging\LoggerFactory;

class XConfigurationFetchedInvoker extends AbstractConfigurationFetchedInvoker
{
    /**
     * @var Core $_core
     */
    private $_core;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * XConfigurationFetchedInvoker constructor.
     * @param Core $_core
     */
    public function __construct(Core $_core)
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_core = $_core;
    }

    protected function internalInvoke(ConfigurationFetchedArgs $cfa)
    {
        // rox.internal.pushUpdates code omitted
        $this->fireConfigurationFetched($cfa);
    }
}
