<?php

namespace Rox\Core\XPack\Configuration;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Configuration\AbstractConfigurationFetchedInvoker;
use Rox\Core\Configuration\ConfigurationFetchedArgs;
use Rox\Core\Configuration\FetcherStatus;
use Rox\Core\Consts\Environment;
use Rox\Core\Core;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Notifications\NotificationListener;

class XConfigurationFetchedInvoker extends AbstractConfigurationFetchedInvoker
{
    /**
     * @var InternalFlagsInterface $_internalFlags
     */
    private $_internalFlags;

    /**
     * @var Core $_core
     */
    private $_core;

    /**
     * @var NotificationListener $_pushUpdatesListener
     */
    private $_pushUpdatesListener;

    /**
     * @var SdkSettingsInterface $_sdkSettings
     */
    private $_sdkSettings;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * XConfigurationFetchedInvoker constructor.
     * @param InternalFlagsInterface $_internalFlags
     * @param Core $_core
     * @param SdkSettingsInterface $_sdkSettings
     */
    public function __construct(
        InternalFlagsInterface $_internalFlags,
        Core $_core,
        SdkSettingsInterface $_sdkSettings)
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_internalFlags = $_internalFlags;
        $this->_core = $_core;
        $this->_sdkSettings = $_sdkSettings;
    }

    protected function internalInvoke(ConfigurationFetchedArgs $cfa)
    {
        try {
            if ($cfa->getFetcherStatus() !== FetcherStatus::ErrorFetchedFailed)
                $this->_startOrStopPushUpdatesListener();
        } catch (Exception $ex) {
            $this->_log->error("Failed to start/stop push server", [
                'exception' => $ex
            ]);
        }

        $this->fireConfigurationFetched($cfa);
    }

    private function _startOrStopPushUpdatesListener()
    {
        if ($this->_internalFlags->IsEnabled("rox.internal.pushUpdates")) {
            if ($this->_pushUpdatesListener == null) {
                $this->_pushUpdatesListener = new NotificationListener(Environment::getNotificationsPath(), $this->_sdkSettings->getApiKey());
                $this->_pushUpdatesListener->on("changed", function ($e) {
                    $this->_core->fetch(true);
                });
                $this->_pushUpdatesListener->start();
            }
        } else {
            if ($this->_pushUpdatesListener != null) {
                $this->_pushUpdatesListener->stop();
                $this->_pushUpdatesListener = null;
            }
        }
    }
}
