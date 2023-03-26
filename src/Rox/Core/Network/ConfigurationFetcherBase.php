<?php

namespace Rox\Core\Network;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Configuration\ConfigurationFetchedInvokerInterface;
use Rox\Core\Configuration\FetcherError;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\Core\Consts\Environment;
use Rox\Core\Consts\PropertyType;

abstract class ConfigurationFetcherBase implements ConfigurationFetcherInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_log;

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
     * @var Environment $_environment
     */
    protected $_environment;

    /**
     * ConfigurationFetcherBase constructor.
     * @param HttpClientInterface $request
     * @param BUIDInterface $buid
     * @param DevicePropertiesInterface $deviceProperties
     * @param ConfigurationFetchedInvokerInterface $configurationFetchedInvoker
     * @param ErrorReporterInterface $errorReporter
     * @param Environment $environment
     */
    public function __construct(
        HttpClientInterface $request,
        BUIDInterface $buid,
        DevicePropertiesInterface $deviceProperties,
        ConfigurationFetchedInvokerInterface $configurationFetchedInvoker,
        ErrorReporterInterface $errorReporter,
        Environment $environment
        )
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_request = $request;
        $this->_buid = $buid;
        $this->_deviceProperties = $deviceProperties;
        $this->_configurationFetchedInvoker = $configurationFetchedInvoker;
        $this->_errorReporter = $errorReporter;
        $this->_environment = $environment;
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
            $this->_log->debug("Failed to parse JSON configuration - Null Or Empty");
            $this->_errorReporter->report("Failed to parse JSON configuration - Null Or Empty", new Exception("data"));
            return null;
        }

        $decoded = json_decode($data, true);
        if ($decoded === null) {
            $this->_configurationFetchedInvoker->invokeWithError(FetcherError::CorruptedJson);
            $this->_log->debug("Failed to parse JSON configuration");
            $this->_errorReporter->report("Failed to parse JSON configuration", new Exception("data"));
            return null;
        }

        return new ConfigurationFetchResult($decoded, $source);
    }

    /**
     * @param array $properties
     * @return string
     */
    private function _getAPIUrl(array $properties)
    {
        return $this->_environment->getConfigAPIPath() . '/' . $properties[PropertyType::getCacheMissRelativeUrl()->getName()];
    }

    /**
     * @param array $properties
     * @return HttpResponseInterface
     */
    protected function _fetchFromAPI(array $properties)
    {
        $url = $this->_getAPIUrl($properties);

        $apiRequest = new RequestData($url, $properties);
        return $this->_request->sendPost($apiRequest);
    }

    /**
     * @param array $properties
     * @return string
     */
    protected function _getPath(array $properties)
    {
        return $properties[PropertyType::getAppKey()->getName()] . '/' . $properties[PropertyType::getBuid()->getName()];
    }

    /**
     * @return array
     */
    protected function _preparePropsFromDeviceProps()
    {
        $queryParams = $this->_deviceProperties->getAllProperties();
        $queryStringParts = $this->_buid->getQueryStringParts();
        foreach (array_keys($queryStringParts) as $key) {
            if (!array_key_exists($key, $queryParams)) {
                $queryParams[$key] = $queryStringParts[$key];
            }
        }
        $queryParams[PropertyType::getCacheMissRelativeUrl()->getName()] = $this->_getPath($queryParams);
        return $queryParams;
    }

    /**
     * @param int $source
     * @param HttpResponseInterface $response
     * @param bool $raiseConfigurationHandler
     * @param int|null $nextSource
     * @see ConfigurationSource
     */
    protected function writeFetchErrorToLogAndInvokeFetchHandler(
        $source,
        HttpResponseInterface $response,
        $raiseConfigurationHandler = true,
        $nextSource = null)
    {
        $retryMsg = '';
        if ($nextSource != null) {
            $retryMsg = "Trying from " . ConfigurationSource::toString($nextSource) . '. ';
        }

        $this->_log->debug("Failed to fetch from " . ConfigurationSource::toString($source) .
            ". " . $retryMsg . "http error code: " . $response->getStatusCode());

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
        $this->_log->error("Failed to fetch configuration. Source: " . ConfigurationSource::toString($source), [
            'exception' => $ex
        ]);

        $this->_configurationFetchedInvoker->invokeWithError(FetcherError::NetworkError);
    }
}
