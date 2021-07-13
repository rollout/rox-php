<?php

namespace Rox\Core\XPack\Network;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Consts\Environment;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\ConfigurationSource;
use Rox\Core\Network\HttpClientInterface;
use Rox\Core\Network\HttpResponseInterface;
use Rox\Core\Network\RequestData;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Utils\DotNetCompat;
use Rox\Core\Utils\MD5Generator;

class StateSender
{
    /**
     * @var HttpClientInterface $_request
     */
    private $_request;

    /**
     * @var DevicePropertiesInterface $_deviceProperties
     */
    private $_deviceProperties;

    /**
     * @var FlagRepositoryInterface $_flagRepository
     */
    private $_flagRepository;

    /**
     * @var CustomPropertyRepositoryInterface $_customPropertyRepository
     */
    private $_customPropertyRepository;

    /**
     * @var PropertyType[] $_relevantAPICallParams
     */
    private $_relevantAPICallParams;

    /**
     * @var PropertyType[] $_stateGenerators
     */
    private $_stateGenerators;

    /**
     * @var LoggerInterface $_logger
     */
    private $_log;

    /**
     * @var bool $_stateSent
     */
    private $_stateSent = false;

    /**
     * StateSender constructor.
     * @param HttpClientInterface $request
     * @param DevicePropertiesInterface $deviceProperties
     * @param FlagRepositoryInterface $flagRepository
     * @param CustomPropertyRepositoryInterface $customPropertyRepository
     */
    public function __construct(
        HttpClientInterface $request,
        DevicePropertiesInterface $deviceProperties,
        FlagRepositoryInterface $flagRepository,
        CustomPropertyRepositoryInterface $customPropertyRepository)
    {
        $this->_relevantAPICallParams = [
            PropertyType::getPlatform(),
            PropertyType::getCustomProperties(),
            PropertyType::getFeatureFlags(),
            PropertyType::getRemoteVariables(),
            PropertyType::getDevModeSecret()
        ];

        $this->_stateGenerators = [
            PropertyType::getPlatform(),
            PropertyType::getAppKey(),
            PropertyType::getCustomPropertiesString(),
            PropertyType::getFeatureFlagsString(),
            PropertyType::getRemoteVariablesString(),
            PropertyType::getDevModeSecret()
        ];

        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);

        $this->_request = $request;
        $this->_deviceProperties = $deviceProperties;
        $this->_flagRepository = $flagRepository;
        $this->_customPropertyRepository = $customPropertyRepository;
    }

    /**
     * @param array $properties
     * @return string
     */
    private function _getStateMd5(array $properties)
    {
        return MD5Generator::generate($properties, $this->_stateGenerators);
    }

    /**
     * @param array $props
     * @return string
     */
    private function _getPath(array $props)
    {
        return $props[PropertyType::getAppKey()->getName()] . '/' . $props[PropertyType::getStateMd5()->getName()];
    }

    /**
     * @return array
     */
    private function _serializeFeatureFlags()
    {
        $allFlags = $this->_flagRepository->getAllFlags();
        $keys = array_keys($allFlags);
        sort($keys);
        return array_map(function ($key) use (&$allFlags) {
            $var = /*RoxStringBase*/
                $allFlags[$key];
            return [
                'name' => $var->getName(),
                'defaultValue' => $var->getDefaultValue(),
                'options' => $var->getVariations(),
            ];
        }, $keys);
    }

    /**
     * @return array
     */
    private function _serializeCustomProperties()
    {
        $allCustomProperties = $this->_customPropertyRepository->getAllCustomProperties();
        $keys = array_keys($allCustomProperties);
        sort($keys);
        return array_map(function ($key) use ($allCustomProperties) {
            $value = (string)$allCustomProperties[$key];
            return json_decode($value, true);
        }, $keys);
    }

    /**
     * @param array $properties
     * @return string
     */
    private function _getCDNUrl(array $properties)
    {
        return Environment::getStateCdnPath() . '/' . $this->_getPath($properties);
    }

    /**
     * @param array $properties
     * @return string
     */
    private function _getAPIUrl(array $properties)
    {
        return Environment::getStateApiPath() . '/' . $this->_getPath($properties);
    }

    /**
     * @param array $properties
     * @return HttpResponseInterface
     */
    private function _sendStateToCDN($properties)
    {
        $cdnRequest = new RequestData($this->_getCDNUrl($properties));
        return $this->_request->sendGet($cdnRequest);
    }

    /**
     * @param array $properties
     * @return HttpResponseInterface
     */
    private function _sendStateToAPI(array $properties)
    {
        $url = $this->_getAPIUrl($properties);
        $queryParams = [];

        foreach ($this->_relevantAPICallParams as $prop) {
            $propName = $prop->getName();
            if (array_key_exists($propName, $properties)) {
                $queryParams[$propName] = $properties[$propName];
            }
        }

        $apiRequest = new RequestData($url, $queryParams);
        return $this->_request->sendPost($apiRequest);
    }

    /**
     * @return array
     */
    private function _preparePropsFromDeviceProps()
    {
        $properties = $this->_deviceProperties->getAllProperties();

        $properties[PropertyType::getFeatureFlags()->getName()] = $this->_serializeFeatureFlags();
        $properties[PropertyType::getRemoteVariables()->getName()] = [];
        $properties[PropertyType::getCustomProperties()->getName()] = $this->_serializeCustomProperties();

        $properties[PropertyType::getFeatureFlagsString()->getName()] = DotNetCompat::toJson($properties[PropertyType::getFeatureFlags()->getName()]);
        $properties[PropertyType::getRemoteVariablesString()->getName()] = DotNetCompat::toJson($properties[PropertyType::getRemoteVariables()->getName()]);
        $properties[PropertyType::getCustomPropertiesString()->getName()] = DotNetCompat::toJson($properties[PropertyType::getCustomProperties()->getName()]);

        $stateMD5 = $this->_getStateMd5($properties);
        $properties[PropertyType::getStateMd5()->getName()] = $stateMD5;

        return $properties;
    }

    function send()
    {
        if ($this->_stateSent) {
            return;
        }

        $this->_stateSent = true;
        $properties = $this->_preparePropsFromDeviceProps();
        $shouldRetry = false;
        $source = ConfigurationSource::CDN;

        try {
            $fetchResult = $this->_sendStateToCDN($properties);

            if ($fetchResult->isSuccessfulStatusCode()) {
                $responseAsString = $fetchResult->getContent()->readAsString();
                $responseJSON = json_decode($responseAsString, true);

                if (array_key_exists("result", $responseJSON)) {
                    $responseResultValue = $responseJSON["result"];
                    if ((int)$responseResultValue == 404) {
                        $shouldRetry = true;
                    }
                }

                if (!$shouldRetry) {
                    // success from cdn
                    return;
                }
            }

            if ($shouldRetry ||
                $fetchResult->getStatusCode() == HttpResponseInterface::STATUS_FORBIDDEN ||
                $fetchResult->getStatusCode() == HttpResponseInterface::STATUS_NOT_FOUND) {
                $this->_logSendStateError($source, $fetchResult, ConfigurationSource::API);
                $source = ConfigurationSource::API;

                $fetchResult = $this->_sendStateToAPI($properties);
                if ($fetchResult->isSuccessfulStatusCode()) {
                    // success for api
                    return;
                }
            }

            $this->_logSendStateError($source, $fetchResult);
        } catch (Exception $ex) {
            $this->_logSendStateException($source, $ex);
        }
    }

    /**
     * @return bool
     */
    public function isStateSent()
    {
        return $this->_stateSent;
    }

    /**
     * @param int $source
     * @param HttpResponseInterface $response
     * @param int|null $nextSource
     */
    private function _logSendStateError($source, HttpResponseInterface $response, $nextSource = null)
    {
        $retryMsg = '';
        if ($nextSource != null) {
            $retryMsg = "Trying to send state to " . ConfigurationSource::toString($nextSource) . ". ";
        }

        $this->_log->debug(sprintf("Failed to send state to %s. %shttp result code: %d",
            ConfigurationSource::toString($source),
            $retryMsg, $response->getStatusCode()));
    }

    private function _logSendStateException($source, Exception $ex)
    {
        $this->_log->error(sprintf("Failed to send state. Source: %s", ConfigurationSource::toString($source)),
            [
                'exception' => $ex
            ]);
    }
}
