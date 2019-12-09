<?php

namespace Rox\Core;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\DynamicApi;
use Rox\Core\Client\DynamicApiInterface;
use Rox\Core\Client\InternalFlags;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Client\RoxOptionsInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Configuration\ConfigurationFetchedInvoker;
use Rox\Core\Configuration\ConfigurationFetchedInvokerInterface;
use Rox\Core\Configuration\ConfigurationParser;
use Rox\Core\Configuration\FetcherStatus;
use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\CustomProperty;
use Rox\Core\CustomProperties\CustomPropertyRepository;
use Rox\Core\CustomProperties\DynamicProperties;
use Rox\Core\CustomProperties\DynamicPropertiesInterface;
use Rox\Core\Entities\EntitiesProviderInterface;
use Rox\Core\Entities\FlagSetter;
use Rox\Core\Entities\RoxContainerInterface;
use Rox\Core\Impression\ImpressionInvoker;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\ConfigurationFetcher;
use Rox\Core\Network\ConfigurationFetcherInterface;
use Rox\Core\Network\ConfigurationFetcherRoxy;
use Rox\Core\Network\ConfigurationFetchResult;
use Rox\Core\Network\GuzzleHttpClientFactory;
use Rox\Core\Register\Registerer;
use Rox\Core\Reporting\ErrorReporterInterface;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;
use Rox\Core\Repositories\ExperimentRepository;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Repositories\TargetGroupRepository;
use Rox\Core\Repositories\TargetGroupRepositoryInterface;
use Rox\Core\Roxx\ExperimentsExtensions;
use Rox\Core\Roxx\Parser;
use Rox\Core\Roxx\ParserInterface;
use Rox\Core\Roxx\PropertiesExtensions;
use Rox\Core\Security\APIKeyVerifier;
use Rox\Core\Security\SignatureVerifier;
use Rox\Core\XPack\Client\XBUID;
use Rox\Core\XPack\Configuration\XConfigurationFetchedInvoker;
use Rox\Core\XPack\Network\StateSender;
use Rox\Core\XPack\Reporting\XErrorReporter;
use Rox\Core\XPack\Security\XAPIKeyVerifier;
use Rox\Core\XPack\Security\XSignatureVerifier;

class Core
{
    /**
     * @var Registerer $_registerer
     */
    private $_registerer;

    /**
     * @var FlagRepositoryInterface $_flagRepository
     */
    private $_flagRepository;

    /**
     * @var CustomPropertyRepositoryInterface $_customPropertyRepository
     */
    private $_customPropertyRepository;

    /**
     * @var ExperimentRepositoryInterface $_experimentRepository
     */
    private $_experimentRepository;

    /**
     * @var TargetGroupRepositoryInterface $_targetGroupRepository
     */
    private $_targetGroupRepository;

    /**
     * @var FlagSetter $_flagSetter
     */
    private $_flagSetter;

    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var ImpressionInvokerInterface $_impressionInvoker
     */
    private $_impressionInvoker;

    /**
     * @var ConfigurationFetchedInvokerInterface $_configurationFetchedInvoker
     */
    private $_configurationFetchedInvoker;

    /**
     * @var SdkSettingsInterface $_sdkSettings
     */
    private $_sdkSettings;

    /**
     * @var ConfigurationFetcherInterface $_configurationFetcher
     */
    private $_configurationFetcher;

    /**
     * @var StateSender $_stateSender
     */
    private $_stateSender = null;

    /**
     * @var ErrorReporterInterface $_errorReporter
     */
    private $_errorReporter;

    /**
     * @var ConfigurationFetchResult $_lastConfigurations
     */
    private $_lastConfigurations = null;

    /**
     * @var InternalFlagsInterface $_internalFlags
     */
    private $_internalFlags;

    /**
     * @var DevicePropertiesInterface $_deviceProperties
     */
    private $_deviceProperties;

    /**
     * @var ConfigurationParser $_configurationParser
     */
    private $_configurationParser;

    /**
     * @var int $_lastFetchTime Unix timestamp in seconds.
     */
    private $_lastFetchTime = 0;

    /**
     * @var DynamicPropertiesInterface $_dynamicProperties
     */
    private $_dynamicProperties;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * Core constructor.
     */
    public function __construct()
    {
        $this->_flagRepository = new FlagRepository();
        $this->_customPropertyRepository = new CustomPropertyRepository();
        $this->_targetGroupRepository = new TargetGroupRepository();
        $this->_experimentRepository = new ExperimentRepository();
        $this->_dynamicProperties = new DynamicProperties();
        $this->_parser = new Parser();
        $experimentsExtensions = new ExperimentsExtensions($this->_parser, $this->_targetGroupRepository, $this->_flagRepository, $this->_experimentRepository);
        $propertiesExtensions = new PropertiesExtensions($this->_parser, $this->_customPropertyRepository, $this->_dynamicProperties);
        $experimentsExtensions->extend();
        $propertiesExtensions->extend();
        $this->_registerer = new Registerer($this->_flagRepository);
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
    }

    /**
     * @param SdkSettingsInterface $sdkSettings
     * @param DevicePropertiesInterface $deviceProperties
     * @param RoxOptionsInterface|null $roxOptions
     */
    public function setup(
        SdkSettingsInterface $sdkSettings,
        DevicePropertiesInterface $deviceProperties,
        $roxOptions)
    {
        $roxyUrl = ($roxOptions != null) ? $roxOptions->getRoxyURL() : null;
        if ($roxyUrl == null) {
            $validApiKeyPattern = "/^[a-f\\d]{24}$/i";
            if (!$sdkSettings->getApiKey()) {
                throw new InvalidArgumentException("Invalid rollout apikey - must be specified");
            }
            if (!preg_match($validApiKeyPattern, $sdkSettings->getApiKey())) {
                throw new InvalidArgumentException("Illegal rollout apikey");
            }
        }

        $this->_sdkSettings = $sdkSettings;

        $this->_deviceProperties = $deviceProperties;

        $httpClientFactory = $roxOptions != null
            ? $roxOptions->getHttpClientFactory()
            : null;

        if ($httpClientFactory == null) {
            $httpClientFactory = new GuzzleHttpClientFactory();
        }

        $request = $httpClientFactory->createHttpClient();
        $reportRequest = $httpClientFactory->createHttpClient();

        $this->_internalFlags = new InternalFlags($this->_experimentRepository, $this->_parser);
        $buid = new XBUID($sdkSettings, $deviceProperties);

        $signature = null;
        $apiKeyVerifier = null;

        $this->_errorReporter = new XErrorReporter($reportRequest, $deviceProperties, $buid);

        if ($roxyUrl != null) {
            $this->_configurationFetchedInvoker = new ConfigurationFetchedInvoker();
            $this->_configurationFetcher = new ConfigurationFetcherRoxy($request, $deviceProperties, $buid, $this->_configurationFetchedInvoker, $roxyUrl, $this->_errorReporter);
            $this->_impressionInvoker = new ImpressionInvoker();
            $signature = new SignatureVerifier();
            $apiKeyVerifier = new APIKeyVerifier();
        } else {
            $this->_stateSender = new StateSender($request, $deviceProperties, $this->_flagRepository, $this->_customPropertyRepository);
            $this->_configurationFetchedInvoker = new XConfigurationFetchedInvoker($this);
            $this->_configurationFetcher = new ConfigurationFetcher($request, $buid, $deviceProperties, $this->_configurationFetchedInvoker, $this->_errorReporter);
            $this->_impressionInvoker = new XImpressionInvoker($this->_internalFlags, $this->_customPropertyRepository, null);
            $signature = new XSignatureVerifier();
            $apiKeyVerifier = new XAPIKeyVerifier($sdkSettings);
        }

        if ($roxOptions != null && $roxOptions->getConfigurationFetchedHandler() != null) {
            $this->_configurationFetchedInvoker->register($roxOptions->getConfigurationFetchedHandler());
        }

        $this->_configurationParser = new ConfigurationParser($signature, $apiKeyVerifier, $this->_errorReporter, $this->_configurationFetchedInvoker);
        $this->_flagSetter = new FlagSetter($this->_flagRepository, $this->_parser, $this->_experimentRepository, $this->_impressionInvoker);

        $this->fetch();

        if ($roxOptions != null && $roxOptions->getImpressionHandler() != null) {
            $this->_impressionInvoker->register($roxOptions->getImpressionHandler());
        }

        if ($roxOptions != null && $roxOptions->getDynamicPropertiesRule() != null) {
            $this->_dynamicProperties->setDynamicPropertiesRule($roxOptions->getDynamicPropertiesRule());
        }

        if ($this->_stateSender != null) {
            $this->_stateSender->send();
        }
    }

    /**
     * @param bool $isSourcePushing
     */
    public function fetch($isSourcePushing = false)
    {
        if ($this->_configurationFetcher == null) {
            return;
        }

        $fetchThrottleInterval = $this->_internalFlags->getIntValue("rox.internal.throttleFetchInSeconds");
        if ($fetchThrottleInterval > 0 && (!$isSourcePushing || $this->_internalFlags->isEnabled("rox.internal.considerThrottleInPush"))) {
            $currentTime = time();

            if ($currentTime < $this->_lastFetchTime + $fetchThrottleInterval) {
                $this->_log->warning("Skipping fetch - kill switch");
                return;
            }

            $this->_lastFetchTime = $currentTime;
        }

        $result = $this->_configurationFetcher->fetch();

        if ($result == null || $result->getSource() == null) {
            return;
        }

        $configuration = $this->_configurationParser->parse($result, $this->_sdkSettings);
        if ($configuration != null) {
            $this->_experimentRepository->setExperiments($configuration->getExperiments());
            $this->_targetGroupRepository->setTargetGroups($configuration->getTargetGroups());
            $this->_flagSetter->setExperiments();

            $hasChanges = ($this->_lastConfigurations == null || $this->_lastConfigurations->equals($result));
            $this->_lastConfigurations = $result;
            $this->_configurationFetchedInvoker->invoke(FetcherStatus::AppliedFromNetwork,
                $configuration->getSignatureDate(),
                $hasChanges);
        }
    }

    /**
     * @param string $ns
     * @param RoxContainerInterface $roxContainer
     */
    public function register($ns, RoxContainerInterface $roxContainer)
    {
        $this->_registerer->registerInstance($roxContainer, $ns);
    }

    /**
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        foreach (array_values($this->_flagRepository->getAllFlags()) as $flag) {
            $flag->setContext($context);
        }
    }

    /**
     * @param CustomProperty $property
     */
    public function addCustomProperty(CustomProperty $property)
    {
        $this->_customPropertyRepository->addCustomProperty($property);
    }

    /**
     * @param CustomProperty $property
     */
    public function addCustomPropertyIfNotExists(CustomProperty $property)
    {
        $this->_customPropertyRepository->addCustomPropertyIfNotExists($property);
    }

    /**
     * @param EntitiesProviderInterface $entitiesProvider
     * @return DynamicApiInterface
     */
    public function dynamicApi(EntitiesProviderInterface $entitiesProvider)
    {
        return new DynamicApi($this->_flagRepository, $entitiesProvider);
    }
}
