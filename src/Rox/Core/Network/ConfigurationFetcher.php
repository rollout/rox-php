<?php

namespace Rox\Core\Network;

use Exception;
use Rox\Core\Consts\PropertyType;

class ConfigurationFetcher extends ConfigurationFetcherBase
{
    /**
     * @param array $properties
     * @return string
     */
    private function _getCDNUrl(array $properties)
    {
        return $this->_environment->getConfigCDNPath() . '/' . $properties[PropertyType::getCacheMissRelativeUrl()->getName()];
    }

    /**
     * @param array $properties
     * @return HttpResponseInterface
     */
    private function _fetchFromCDN(array $properties)
    {
        return $this->_request->sendGet(new RequestData($this->_getCDNUrl($properties), [
            PropertyType::getDistinctId()->getName() =>
                (string)$properties[PropertyType::getDistinctId()->getName()],
            'realPlatform' =>
                (string)@$properties['platform'],
            'sdkVersion' =>
                (string)@$properties['lib_version'],
            'platformVersion' =>
                php_sapi_name(),
            'languageVersion' =>
                PHP_VERSION
        ]));
    }

    /**
     * @return ConfigurationFetchResult|null
     */
    function fetch()
    {
        $shouldRetry = false;
        $source = ConfigurationSource::CDN;
        $configurationFetchResult = null;

        try {
            $properties = $this->_preparePropsFromDeviceProps();
            $fetchResult = $this->_fetchFromCDN($properties);

            if ($fetchResult->isSuccessfulStatusCode()) {
                $responseAsString = $fetchResult->getContent()->readAsString();
                $configurationFetchResult = $this->createConfigurationResult($responseAsString, $source);

                if ($configurationFetchResult == null || $configurationFetchResult->getParsedData() == null) {
                    return null;
                }

                $parsedData = $configurationFetchResult->getParsedData();
                if (array_key_exists("result", $parsedData)) {
                    $responseResultValue = $parsedData["result"];
                    if (((int)$responseResultValue) == 404) {
                        $shouldRetry = true;
                    }
                }

                if (!$shouldRetry) {
                    // success from cdn
                    return $configurationFetchResult;
                }
            }

            if ($shouldRetry ||
                $fetchResult->getStatusCode() == 403 /*Forbidden*/ ||
                $fetchResult->getStatusCode() == 404 /*NotFound*/) {
                $this->writeFetchErrorToLogAndInvokeFetchHandler($source, $fetchResult, false, ConfigurationSource::API);
                $source = ConfigurationSource::API;

                $fetchResult = $this->_fetchFromAPI($properties);
                if ($fetchResult->isSuccessfulStatusCode()) {
                    return $this->createConfigurationResult($fetchResult->getContent()->readAsString(), $source);
                }
            }

            $this->writeFetchErrorToLogAndInvokeFetchHandler($source, $fetchResult);
        } catch (Exception $ex) {
            $this->writeFetchExceptionToLogAndInvokeFetchHandler($source, $ex);
        }
        return null;
    }
}