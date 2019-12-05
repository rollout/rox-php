<?php

namespace Rox\Core\XPack\Configuration;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Configuration\AbstractConfigurationFetchedInvoker;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Configuration\FetcherStatus;
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
        try {
            if ($cfa->getFetcherStatus() !== FetcherStatus::ErrorFetchedFailed) {
                $this->_core->fetch(true);
            }
        } catch (Exception $ex) {
            $this->_log->error("Failed to fetch configuration", [
                'exception' => $ex
            ]);
        }

        $this->fireConfigurationFetched($cfa);
    }
}
