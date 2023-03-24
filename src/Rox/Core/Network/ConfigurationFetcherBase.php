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

abstract class ConfigurationFetcherBase implements ConfigurationFetcherInterface
{
    /**
     * @var LoggerInterface
     */
    private $_log;

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
