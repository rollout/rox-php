<?php

namespace Rox\Core;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use InvalidArgumentException;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use Kevinrob\GuzzleCache\Strategy\Delegate\DelegatingCacheStrategy;
use Kevinrob\GuzzleCache\Strategy\NullCacheStrategy;
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
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvoker;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;
use Rox\Core\Impression\ImpressionInvoker;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Network\CdnCacheStrategy;
use Rox\Core\Network\CdnRequestMatcher;
use Rox\Core\Network\ConfigurationFetcher;
use Rox\Core\Network\ConfigurationSource;
use Rox\Core\Network\ConfigurationFetcherInterface;
use Rox\Core\Network\ConfigurationFetcherOneSource;
use Rox\Core\Network\ConfigurationFetchResult;
use Rox\Core\Network\GuzzleHttpClientFactory;
use Rox\Core\Network\GuzzleHttpClientOptions;
use Rox\Core\Network\HttpClientFactoryInterface;
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
use Rox\Core\XPack\Analytics\AnalyticsClient;
use Rox\Core\XPack\Client\XBUID;
use Rox\Core\XPack\Network\StateSender;
use Rox\Core\XPack\Reporting\XErrorReporter;
use Rox\Core\Reporting\ErrorReporter;
use Rox\Core\XPack\Security\XAPIKeyVerifier;
use Rox\Core\XPack\Security\XSignatureVerifier;
use Rox\Core\Consts\Environment;
use Rox\Core\Utils\ApiKeyHelpers;

final class Core
{
    const MIN_CACHE_TTL_SECONDS = 30;
    const STATE_STORE_CACHE_TTL_SECONDS = 31556952; // 1 year

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
     * @var UserspaceUnhandledErrorInvokerInterface $_userUnhandledErrorInvoker
     */
    private $_userUnhandledErrorInvoker;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * @var Environment $_environment
     */
    private $_environment;

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
        $this->_userUnhandledErrorInvoker = new UserspaceUnhandledErrorInvoker();
        $this->_parser = new Parser($this->_userUnhandledErrorInvoker);
        $experimentsExtensions = new ExperimentsExtensions($this->_parser, $this->_targetGroupRepository, $this->_flagRepository, $this->_experimentRepository);
        $propertiesExtensions = new PropertiesExtensions($this->_parser, $this->_customPropertyRepository, $this->_dynamicProperties);
        $experimentsExtensions->extend();
        $propertiesExtensions->extend();
        $this->_registerer = new Registerer($this->_flagRepository);
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
    }

    public function setUserspaceUnhandledErrorHandler(callable $userspaceUnhandledErrorHandler)
    {
        $this->_userUnhandledErrorInvoker->register($userspaceUnhandledErrorHandler);
    }

    /**
     * @param SdkSettingsInterface $sdkSettings
     * @param DevicePropertiesInterface $deviceProperties
     * @param RoxOptionsInterface|null $roxOptions
     */
    public function setup(
        SdkSettingsInterface $sdkSettings,
        DevicePropertiesInterface $deviceProperties,
        RoxOptionsInterface $roxOptions = null
    ) {
        $roxyUrl = ($roxOptions != null) ? $roxOptions->getRoxyURL() : null;
        $this->_environment = new Environment($roxOptions);

        if ($roxyUrl == null) {
            if (!$sdkSettings->getApiKey()) {
                throw new InvalidArgumentException("Invalid rollout apikey - must be specified");
            }
            if (!ApiKeyHelpers::isValidKey($sdkSettings->getApiKey())) {
                throw new InvalidArgumentException("Illegal rollout apikey");
            }

            /*
             * Checks if either roxOptions is missing, and if not checks if there are no network configs already defined
             * This is done to avoid override of the given network options
             */
            $_isNetworkConfigMissing = $roxOptions == null || ($roxOptions != null && $roxOptions->getNetworkConfigurationsOptions() == null);

            if ($_isNetworkConfigMissing && ApiKeyHelpers::isCBPApiKey($sdkSettings->getApiKey())) {
                $this->_environment->setToPlatform();
            }
        }

        $this->_sdkSettings = $sdkSettings;
        $this->_deviceProperties = $deviceProperties;

        $cacheTtl = $roxOptions != null
            ? $roxOptions->getConfigFetchIntervalInSeconds() :
            self::MIN_CACHE_TTL_SECONDS;

        $httpClientFactory = $this->_createHttpClientFactory($roxOptions, $cacheTtl);

        $httpClient = $httpClientFactory->createHttpClient();
        $this->_internalFlags = new InternalFlags($this->_experimentRepository, $this->_parser);
        $buid = new XBUID($sdkSettings, $deviceProperties);

        $signature = null;
        $apiKeyVerifier = null;

        if ($this->_environment->errorReporterPath() == null) {
            $this->_errorReporter = new ErrorReporter();
        } else {
            $this->_errorReporter = new XErrorReporter($httpClientFactory->createHttpClient(), $deviceProperties, $buid, $this->_environment);
        }
        $this->_configurationFetchedInvoker = new ConfigurationFetchedInvoker($this->_userUnhandledErrorInvoker);

        if ($roxyUrl != null) {
            $this->_configurationFetcher = new ConfigurationFetcherOneSource($httpClient, $buid, $deviceProperties, $this->_configurationFetchedInvoker, $this->_errorReporter, $this->_environment, ConfigurationSource::Roxy);
            $this->_impressionInvoker = new ImpressionInvoker($this->_userUnhandledErrorInvoker);
            $signature = new SignatureVerifier();
            $apiKeyVerifier = new APIKeyVerifier();
        } else {
            $stateSenderHttpClient = $this->_createHttpClientFactory($roxOptions, self::STATE_STORE_CACHE_TTL_SECONDS)
                ->createHttpClient();
            if ($this->_environment->sendStateAPIPath() != null) {
                $this->_stateSender = new StateSender($stateSenderHttpClient, $deviceProperties, $this->_flagRepository, $this->_customPropertyRepository, $this->_environment);
            }
            if ($this->_environment->getConfigCDNPath() == null) {
                $this->_configurationFetcher = new ConfigurationFetcherOneSource($httpClient, $buid, $deviceProperties, $this->_configurationFetchedInvoker, $this->_errorReporter, $this->_environment, ConfigurationSource::API);
            } else {
                $this->_configurationFetcher = new ConfigurationFetcher($httpClient, $buid, $deviceProperties, $this->_configurationFetchedInvoker, $this->_errorReporter, $this->_environment);
            }

            if ($this->_environment->analyticsPath() != null) {
                $analyticsClient = new AnalyticsClient($this->_deviceProperties, $this->_internalFlags, $httpClientFactory->createHttpClient(), $this->_environment);
                $this->_impressionInvoker = new XImpressionInvoker(
                    $this->_internalFlags,
                    $this->_userUnhandledErrorInvoker,
                    $this->_customPropertyRepository,
                    $analyticsClient
                );
            }
            $signature = new XSignatureVerifier();
            $apiKeyVerifier = new XAPIKeyVerifier($sdkSettings);
        }

        if ($roxOptions != null && $roxOptions->getConfigurationFetchedHandler() != null) {
            $this->_configurationFetchedInvoker->register($roxOptions->getConfigurationFetchedHandler());
        }

        $this->_configurationParser = new ConfigurationParser($signature, $apiKeyVerifier, $this->_errorReporter, $this->_configurationFetchedInvoker, $roxOptions);
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
            $this->_configurationFetchedInvoker->invoke(
                FetcherStatus::AppliedFromNetwork,
                $configuration->getSignatureDate(),
                $hasChanges
            );
        }
    }

    /**
     * @param string $ns
     * @param object $roxContainer
     */
    public function register($ns, $roxContainer)
    {
        $this->_registerer->registerInstance($roxContainer, $ns);
    }

    /**
     * @param ContextInterface|null $context
     */
    public function setContext($context)
    {
        $this->_parser->setGlobalContext($context);
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

    /**
     * @param RoxOptionsInterface|null $options
     * @param int|null $cacheTtl
     * @return HttpClientFactoryInterface
     */
    private function _createHttpClientFactory($options, $cacheTtl)
    {
        $httpClientOptions = new GuzzleHttpClientOptions();
        if ($options != null) {
            $httpClientOptions->setTimeout($options->getTimeout());
        }
        $cacheStorage = $options
            ? $options->getCacheStorage()
            : null;
        if (!$cacheStorage) {
            $cacheStorage = new DoctrineCacheStorage(
                new ChainCache([
                    new ArrayCache(),
                    new FilesystemCache(join(DIRECTORY_SEPARATOR, [
                        sys_get_temp_dir(),
                        'rollout',
                        'cache'
                    ])),
                ])
            );
        }
        $strategy = new DelegatingCacheStrategy(new NullCacheStrategy());
        $strategy->registerRequestMatcher(
            new CdnRequestMatcher($this->_environment),
            new CdnCacheStrategy(
                $cacheStorage,
                max($cacheTtl ?: self::MIN_CACHE_TTL_SECONDS, self::MIN_CACHE_TTL_SECONDS)
            )
        );
        $httpClientOptions->addMiddleware(new CacheMiddleware($strategy), 'cache');
        $httpClientOptions->setLogCacheHitsAndMisses($options
            ? $options->isLogCacheHitsAndMisses()
            : false);
        $httpClientOptions->setUserAgent("rox-php/{$this->_deviceProperties->getLibVersion()}");
        return new GuzzleHttpClientFactory($httpClientOptions);
    }
}
