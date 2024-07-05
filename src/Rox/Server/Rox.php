<?php

namespace Rox\Server;

use Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Rox\Core\Client\DynamicApiInterface;
use Rox\Core\Client\SdkSettings;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Core;
use Rox\Core\CustomProperties\CustomProperty;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\CustomProperties\DeviceProperty;
use Rox\Core\Logging\LoggerFactory;
use Rox\Server\Client\ServerProperties;
use Rox\Server\Flags\ServerEntitiesProvider;
use RuntimeException;

final class Rox
{
    /**
     * @var Core $_core
     */
    private static $_core;

    /**
     * @var LoggerInterface $_log
     */
    private static $_log;

    /**
     * @var int $_state
     * @see RoxState
     */
    private static $_state = RoxState::Idle;

    /**
     * @return int
     * @see RoxState
     */
    public static function getState()
    {
        return self::$_state;
    }

    /**
     * @return Core
     */
    private static function getCore()
    {
        if (!self::$_core) {
            self::$_core = new Core();
        }
        return self::$_core;
    }

    /**
     * @return LoggerInterface
     */
    private static function getLog()
    {
        if (!self::$_log) {
            self::$_log = LoggerFactory::getInstance()->createLogger(self::class);
        }
        return self::$_log;
    }

    /**
     * @param string $apiKey
     * @param RoxOptions|null $roxOptions
     */
    public static function setup($apiKey, RoxOptions $roxOptions = null)
    {
        if (
            self::$_state !== RoxState::Idle &&
            self::$_state !== RoxState::Corrupted
        ) {
            self::getLog()->warning("Rox was already initialized, skipping Setup");
            return;
        }

        if (self::$_state === RoxState::Corrupted) {
            self::reset();
        }

        if (self::$_state === RoxState::Idle) {
            self::$_state = RoxState::SettingUp;

            try {
                if (!$roxOptions) {
                    $roxOptionsBuilder = new RoxOptionsBuilder();
                    $uuidApiKeyPattern = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i";
                    if (preg_match($uuidApiKeyPattern, $apiKey)) {
                        $roxOptionsBuilder->setDisableSignatureVerification(true);
                    }
                    $roxOptions = new RoxOptions($roxOptionsBuilder);
                }

                $sdkSettings = new SdkSettings($apiKey, $roxOptions->getDevModeKey());
                $serverProperties = new ServerProperties($sdkSettings, $roxOptions);

                $props = $serverProperties->getAllProperties();
                $core = self::getCore();
                $core->addCustomPropertyIfNotExists(new DeviceProperty(PropertyType::getPlatform()->getName(), CustomPropertyType::getString(), (string) $props[PropertyType::getPlatform()->getName()]));
                $core->addCustomPropertyIfNotExists(new DeviceProperty(PropertyType::getAppRelease()->getName(), CustomPropertyType::getSemver(), (string) $props[PropertyType::getAppRelease()->getName()]));
                $core->addCustomPropertyIfNotExists(new DeviceProperty(PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), function ($c) {
                    return Uuid::uuid4()->toString();
                }));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal.realPlatform", CustomPropertyType::getString(), (string) $props[PropertyType::getPlatform()->getName()]));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal.customPlatform", CustomPropertyType::getString(), (string) $props[PropertyType::getPlatform()->getName()]));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal.appKey", CustomPropertyType::getString(), $serverProperties->getRolloutKey()));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getLibVersion()->getName(), CustomPropertyType::getSemver(), (string) $props[PropertyType::getLibVersion()->getName()]));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getApiVersion()->getName(), CustomPropertyType::getSemver(), (string) $props[PropertyType::getApiVersion()->getName()]));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), function ($c) {
                    return Uuid::uuid4()->toString();
                }));
                $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getDateTime()->getName(), CustomPropertyType::getDateTime(), function ($c) {
                    $now = new \DateTime("now");
                    return $now;
                }));

                $core->setup($sdkSettings, $serverProperties, $roxOptions);
                self::$_state = RoxState::Set;

            } catch (Exception $ex) {
                self::$_state = RoxState::Corrupted;
                self::getLog()->error("Failed in Rox::setup", [
                    'exception' => $ex
                ]);
                throw new RuntimeException("Rox::setup failed. see innerException", $ex);
            }
        }
    }

    public static function shutdown()
    {
        if (
            self::$_state !== RoxState::Set &&
            self::$_state !== RoxState::Corrupted
        ) {
            self::getLog()->warning("Rox can only be shutdown when it is already Set up, skipping Shutdown");
        } else {
            self::reset();
        }
    }

    private static function reset()
    {
        self::$_state = RoxState::ShuttingDown;
        // TODO: call some core->shutdown or something?
        self::$_core = null;
        self::$_state = RoxState::Idle;
    }

    /**
     * @param string $namespace
     * @param object $roxContainer
     */
    public static function register()
    {
        if (func_num_args() == 2) {
            self::getCore()->register(
                func_get_arg(0), // ns
                func_get_arg(1)
            ); // container
        } else {
            self::getCore()->register(
                "",
                func_get_arg(0)
            );
        }

    }

    /**
     * @param ContextInterface|null $context
     */
    public static function setContext($context)
    {
        self::getCore()->setContext($context);
    }

    public static function fetch()
    {
        self::getCore()->fetch();
    }

    /**
     * @param string $name
     * @param string $value
     */
    public static function setCustomStringProperty($name, $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getString(), $value));
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public static function setCustomComputedStringProperty($name, callable $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getString(), $value));
    }

    /**
     * @param string $name
     * @param bool $value
     */
    public static function setCustomBooleanProperty($name, $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getBool(), $value));
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public static function setCustomComputedBooleanProperty($name, callable $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getBool(), $value));
    }

    /**
     * @param string $name
     * @param int $value
     */
    public static function setCustomIntegerProperty($name, $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getInt(), $value));
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public static function setCustomComputedIntegerProperty($name, callable $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getInt(), $value));
    }

    /**
     * @param string $name
     * @param double $value
     */
    public static function setCustomDoubleProperty($name, $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getDouble(), $value));
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public static function setCustomComputedDoubleProperty($name, callable $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getDouble(), $value));
    }

    /**
     * @param string $name
     * @param string $value
     */
    public static function setCustomSemverProperty($name, $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getSemver(), $value));
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public static function setCustomComputedSemverProperty($name, callable $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getSemver(), $value));
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public static function setCustomDateTimeProperty($name, callable $value)
    {
        self::getCore()->addCustomProperty(new CustomProperty($name, CustomPropertyType::getDateTime(), $value));
    }

    /**
     * @param callable $userspaceUnhandledErrorHandler
     */
    public static function setUserspaceUnhandledErrorHandler(callable $userspaceUnhandledErrorHandler)
    {
        self::getCore()->setUserspaceUnhandledErrorHandler($userspaceUnhandledErrorHandler);
    }

    /**
     * @return DynamicApiInterface
     */
    public static function dynamicApi()
    {
        return self::getCore()->dynamicApi(new ServerEntitiesProvider());
    }
}
