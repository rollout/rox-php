<?php

namespace Rox\Core\Network;

use Exception;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Configuration\ConfigurationFetchedInvokerInterface;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\Core\Consts\Environment;

class ConfigurationFetcherOneSource extends ConfigurationFetcherBase
{
    private $_source;

     /**
     * ConfigurationFetcherOneSource constructor.
     * @param HttpClientInterface $request
     * @param DevicePropertiesInterface $deviceProperties
     * @param BUIDInterface $buid
     * @param ConfigurationFetchedInvokerInterface $configurationFetchedInvoker
     * @param ErrorReporterInterface $errorReporter
     * @param Environment $environment
     * @param int source (see ConfigurationSource)
     */
    public function __construct(
        HttpClientInterface $request,
        BUIDInterface $buid,
        DevicePropertiesInterface $deviceProperties,
        ConfigurationFetchedInvokerInterface $configurationFetchedInvoker,
        ErrorReporterInterface $errorReporter,
        Environment $environment,
        int $source = ConfigurationSource::Roxy)
    {
        parent::__construct($request, $buid, $deviceProperties, $configurationFetchedInvoker, $errorReporter, $environment);
        $this->_source = $source;
    }

    /**
     * @return int
     * @see ConfigurationSource
     */
    protected function getSource()
    {
        return $this->_source;
    }

    function fetch()
    {
        $usedSource = $this->getSource();
        try {
            $properties = $this->_preparePropsFromDeviceProps();
            $fetchResult = $this->_fetchFromAPI($properties);
            if ($fetchResult->getStatusCode() == 200) {
                return $this->createConfigurationResult($fetchResult->getContent()->readAsString(), $usedSource);
            } else {
                $this->writeFetchErrorToLogAndInvokeFetchHandler($usedSource, $fetchResult);
            }
        } catch (Exception $ex) {
            $this->writeFetchExceptionToLogAndInvokeFetchHandler($usedSource, $ex);
        }

        return null;
    }
}
