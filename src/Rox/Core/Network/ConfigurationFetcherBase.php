<?php

namespace Rox\Core\Network;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Configuration\ConfigurationFetchedInvokerInterface;
use Rox\Core\Configuration\FetcherError;
use Rox\Core\Reporting\ErrorReporterInterface;

abstract class ConfigurationFetcherBase implements ConfigurationFetcherInterface
{
    /**
     * @var HttpClientInterface $_request
     */
    protected $_request;

    /**
     * @var BUIDInterface $_buid
     */
    protected $_buid;

    /**
     * @var DevicePropertiesInterface $_deviceProperties
     */
    protected $_deviceProperties;
    /**
     * @var ConfigurationFetchedInvokerInterface $_configurationFetchedInvoker
     */
    protected $_configurationFetchedInvoker;

    /**
     * @var ErrorReporterInterface $_errorReporter
     */
    protected $_errorReporter;

    /**
     * ConfigurationFetcherBase constructor.
     * @param HttpClientInterface $request
     * @param BUIDInterface $buid
     * @param DevicePropertiesInterface $deviceProperties
     * @param ConfigurationFetchedInvokerInterface $configurationFetchedInvoker
     * @param ErrorReporterInterface $errorReporter
     */
    public function __construct(
        HttpClientInterface $request,
        BUIDInterface $buid,
        DevicePropertiesInterface $deviceProperties,
        ConfigurationFetchedInvokerInterface $configurationFetchedInvoker,
        ErrorReporterInterface $errorReporter)
    {
        $this->_request = $request;
        $this->_buid = $buid;
        $this->_deviceProperties = $deviceProperties;
        $this->_configurationFetchedInvoker = $configurationFetchedInvoker;
        $this->_errorReporter = $errorReporter;
    }

    /**
     * @param string $data
     * @param int $source
     * @return ConfigurationFetchResult|null
     * @see ConfigurationSource
     */
    protected function createConfigurationResult($data, $source)
    {
        if (!$data) {
            $this->_configurationFetchedInvoker->invokeWithError(FetcherError::EmptyJson);
            // TODO: $log->error
            error_log("Failed to parse JSON configuration - Null Or Empty");
            $this->_errorReporter->report("Failed to parse JSON configuration - Null Or Empty", new Exception("data"));
            return null;
        }

        $decoded = json_decode($data, true);
        if ($decoded === null) {
            $this->_configurationFetchedInvoker->invokeWithError(FetcherError::CorruptedJson);
            // TODO: $log->error
            $this->_errorReporter->report("Failed to parse JSON configuration", new Exception("data"));
            return null;
        }

        return new ConfigurationFetchResult($decoded, $source);
    }

    /**
     * @param int $source
     * @param ResponseInterface $response
     * @param bool $raiseConfigurationHandler
     * @param int|null $nextSource
     * @see ConfigurationSource
     */
    protected function writeFetchErrorToLogAndInvokeFetchHandler(
        $source,
        ResponseInterface $response,
        $raiseConfigurationHandler = true,
        $nextSource = null)
    {
        $retryMsg = '';
        if ($nextSource != null) {
            $retryMsg = "Trying from " . ConfigurationSource::toString($nextSource) . '. ';
        }

        // TODO: $log->debug
        error_log("Failed to fetch from " . ConfigurationSource::toString($source) .
            ". " . $retryMsg . "http error code: " . $response->getStatusCode(), E_ERROR);

        if ($raiseConfigurationHandler) {
            $this->_configurationFetchedInvoker->invokeWithError(FetcherError::NetworkError);
        }
    }

    /**
     * @param int $source
     * @param Exception $ex
     * @see ConfigurationSource
     */
    protected function writeFetchExceptionToLogAndInvokeFetchHandler($source, Exception $ex)
    {
        // TODO: $log->error
        error_log("Failed to fetch configuration. Source: " . ConfigurationSource::toString($source) . ' ' . $ex);

        $this->_configurationFetchedInvoker->invokeWithError(FetcherError::NetworkError);
    }
}
