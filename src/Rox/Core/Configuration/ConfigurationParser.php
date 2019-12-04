<?php

namespace Rox\Core\Configuration;

use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Configuration\Models\TargetGroupModel;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\ConfigurationFetchResult;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\Core\Security\APIKeyVerifierInterface;
use Rox\Core\Security\SignatureVerifierInterface;

class ConfigurationParser
{
    /**
     * @$SignatureVerifierInterface $_signatureVerifier
     */
    private $_signatureVerifier;

    /**
     * @$ErrorReporterInterface $_errorReporter
     */
    private $_errorReporter;

    /**
     * @$ConfigurationFetchedInvokerInterface $_configurationFetchedInvoker
     */
    private $_configurationFetchedInvoker;

    /**
     * @$APIKeyVerifierInterface $_apiKeyVerifier
     */
    private $_apiKeyVerifier;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * ConfigurationParser constructor.
     * @param SignatureVerifierInterface $signatureVerifier
     * @param APIKeyVerifierInterface $apiKeyVerifier
     * @param ErrorReporterInterface $errorReporter
     * @param ConfigurationFetchedInvokerInterface $configurationFetchedInvoker
     */
    public function __construct(
        SignatureVerifierInterface $signatureVerifier,
        APIKeyVerifierInterface $apiKeyVerifier,
        ErrorReporterInterface $errorReporter,
        ConfigurationFetchedInvokerInterface $configurationFetchedInvoker)
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_signatureVerifier = $signatureVerifier;
        $this->_errorReporter = $errorReporter;
        $this->_configurationFetchedInvoker = $configurationFetchedInvoker;
        $this->_apiKeyVerifier = $apiKeyVerifier;
    }

    /**
     * @param array $configData
     * @return bool
     */
    protected function isVerifiedSignature($configData)
    {
        if (!isset($configData["data"]) || !isset($configData["signature_v0"])) {
            return false;
        }
        return $this->_signatureVerifier->verify((string)$configData["data"], (string)$configData["signature_v0"]);
    }

    /**
     * @param array $configData
     * @return bool
     */
    protected function isAPIKeyVerified($configData)
    {
        if (!isset($configData["application"])) {
            return false;
        }
        return $this->_apiKeyVerifier->verify((string)$configData["application"]);
    }

    /**
     * @param ConfigurationFetchResult $fetchResult
     * @param SdkSettingsInterface|null $sdkSettings
     * @return Configuration|null
     */
    public function parse(ConfigurationFetchResult $fetchResult, $sdkSettings)
    {
        try {
            $json = $fetchResult->getParsedData();

            if (!$this->isVerifiedSignature($json)) {
                $this->_configurationFetchedInvoker->invokeWithError(FetcherError::SignatureVerificationError);
                $this->_errorReporter->report("Failed to validate signature", new Exception(sprintf("Data : %s Signature : %s",
                    (string)$json["data"], (string)$json["signature_v0"])));
                return null;
            }

            $signatureDate = strtotime($json["signed_date"]) * 1000;
            $internalDataString = (string)$json["data"];
            $internalDataObject = json_decode($internalDataString, true);

            if (!$this->isAPIKeyVerified($internalDataObject)) {
                $this->_configurationFetchedInvoker->invokeWithError(FetcherError::MismatchAppKey);
                $this->_errorReporter->report("Failed to parse JSON configuration - ",
                    new InvalidArgumentException(sprintf("Internal Data: %s SdkSettings: %s",
                        (string)$internalDataObject["application"], $sdkSettings->getApiKey())));
                return null;
            }

            $experiments = $this->_parseExperiments($internalDataObject);
            $targetGroups = $this->_parseTargetGroups($internalDataObject);

            return new Configuration($experiments, $targetGroups, $signatureDate);
        } catch (Exception $ex) {
            $this->_log->error("Failed to parse configurations", [
                'exception' => $ex
            ]);
            $this->_configurationFetchedInvoker->invokeWithError(FetcherError::Unknown);
        }
        return null;
    }

    /**
     * @param array $data
     * @return ExperimentModel[]
     */
    private function _parseExperiments(array $data)
    {
        $experimentsContainer = (array)$data["experiments"];
        return array_map(function ($e) {
            return $this->_parseExperiment($e);
        }, $experimentsContainer);
    }

    /**
     * @param array $data
     * @return ExperimentModel
     */
    private function _parseExperiment(array $data)
    {
        $condition = isset($data["deploymentConfiguration"]) && isset($data["deploymentConfiguration"]["condition"])
            ? (string)$data["deploymentConfiguration"]["condition"]
            : null;
        $isArchived = isset($data["archived"]) ? (bool)$data["archived"] : null;
        $name = isset($data["name"]) ? (string)$data["name"] : null;
        $id = isset($data["_id"]) ? (string)$data["_id"] : null;
        $labels = [];
        if (isset($data["labels"])) {
            $labels = $data["labels"];
        }

        $featureFlagsContainer = isset($data["featureFlags"]) ? $data["featureFlags"] : null;
        $flags = array_map(function ($f) {
            return isset($f["name"]) ? $f["name"] : null;
        }, $featureFlagsContainer);
        $stickinessProperty = isset($data["stickinessProperty"]) ? (string)$data["stickinessProperty"] : null;

        return new ExperimentModel($id, $name, $condition, $isArchived, $flags, $labels, $stickinessProperty);
    }

    /**
     * @param array $data
     * @return TargetGroupModel[]
     */
    private function _parseTargetGroups(array $data)
    {
        $targetGroupsContainer = isset($data["targetGroups"])
            ? $data["targetGroups"]
            : [];
        return array_map(function ($t) {
            return $this->_parseTargetGroup($t);
        }, $targetGroupsContainer);
    }

    /**
     * @param array $data
     * @return TargetGroupModel
     */
    private function _parseTargetGroup(array $data)
    {
        $id = isset($data["_id"]) ? (string)$data["_id"] : null;
        $condition = isset($data["condition"]) ? (string)$data["condition"] : null;
        return new TargetGroupModel($id, $condition);
    }
}
