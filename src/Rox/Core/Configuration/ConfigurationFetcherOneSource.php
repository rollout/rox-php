<?php

namespace Rox\Core\Configuration;

use Exception;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Network\ConfigurationFetcherBase;
use Rox\Core\Network\ConfigurationSource;
use Rox\Core\Network\HttpClientInterface;
use Rox\Core\Network\HttpResponseInterface;
use Rox\Core\Reporting\ErrorReporterInterface;

abstract class ConfigurationFetcherOneSource extends ConfigurationFetcherBase
{
    /**
     * @var string $_url
     */
    protected $_url;

    /**
     * ConfigurationFetcherOneSource constructor.
     * @param HttpClientInterface $request
     * @param DevicePropertiesInterface $deviceProperties
     * @param BUIDInterface $buid
     * @param ConfigurationFetchedInvokerInterface $configurationFetchedInvoker
     * @param string $_url
     * @param ErrorReporterInterface $errorReporter
     */
    public function __construct(
        HttpClientInterface $request,
        DevicePropertiesInterface $deviceProperties,
        BUIDInterface $buid,
        ConfigurationFetchedInvokerInterface $configurationFetchedInvoker,
        $_url,
        ErrorReporterInterface $errorReporter)
    {
        parent::__construct($request, $buid, $deviceProperties, $configurationFetchedInvoker, $errorReporter);

        $this->_url = $_url;
    }

    /**
     * @return int
     * @see ConfigurationSource
     */
    protected abstract function getSource();

    /**
     * @return HttpResponseInterface
     */
    protected abstract function internalFetch();

    final function fetch()
    {
        $usedSource = $this->getSource();

        try {
            $roxyFetchResult = $this->internalFetch();
            if ($roxyFetchResult->getStatusCode() == 200) {
                return $this->createConfigurationResult($roxyFetchResult->getContent()->readAsString(), $usedSource);
            } else {
                $this->writeFetchErrorToLogAndInvokeFetchHandler($usedSource, $roxyFetchResult);
            }
        } catch (Exception $ex) {
            $this->writeFetchExceptionToLogAndInvokeFetchHandler($usedSource, $ex);
        }

        return null;
    }
}
