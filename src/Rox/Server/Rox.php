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

class Rox
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
     * @return Core
     */
    private static function getCore()
    {
        if (self::$_core == null) {
            self::$_core = new Core();
        }
        return self::$_core;
    }

    /**
     * @return LoggerInterface
     */
    private static function getLog()
    {
        if (self::$_log == null) {
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
        try {
            if ($roxOptions == null) {
                $roxOptions = new RoxOptions(new RoxOptionsBuilder());
            }

            $sdkSettings = new SdkSettings($apiKey, $roxOptions->getDevModeKey());
            $serverProperties = new ServerProperties($sdkSettings, $roxOptions);

            $props = $serverProperties->getAllProperties();
            $core = self::getCore();
            $core->addCustomPropertyIfNotExists(new DeviceProperty(PropertyType::getPlatform()->getName(), CustomPropertyType::getString(), (string)$props[PropertyType::getPlatform()->getName()]));
            $core->addCustomPropertyIfNotExists(new DeviceProperty(PropertyType::getAppRelease()->getName(), CustomPropertyType::getSemver(), (string)$props[PropertyType::getAppRelease()->getName()]));
            $core->addCustomPropertyIfNotExists(new DeviceProperty(PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), function ($c) {
                return Uuid::uuid4()->toString();
            }));
            $core->addCustomPropertyIfNotExists(new DeviceProperty("internal.realPlatform", CustomPropertyType::getString(), (string)$props[PropertyType::getPlatform()->getName()]));
            $core->addCustomPropertyIfNotExists(new DeviceProperty("internal.customPlatform", CustomPropertyType::getString(), (string)$props[PropertyType::getPlatform()->getName()]));
            $core->addCustomPropertyIfNotExists(new DeviceProperty("internal.appKey", CustomPropertyType::getString(), $serverProperties->getRolloutKey()));
            $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getLibVersion()->getName(), CustomPropertyType::getSemver(), (string)$props[PropertyType::getLibVersion()->getName()]));
            $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getApiVersion()->getName(), CustomPropertyType::getSemver(), (string)$props[PropertyType::getApiVersion()->getName()]));
            $core->addCustomPropertyIfNotExists(new DeviceProperty("internal." . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), function ($c) {
                return Uuid::uuid4()->toString();
            }));

            $core->setup($sdkSettings, $serverProperties, $roxOptions);
        } catch (Exception $ex) {
            self::getLog()->error("Failed in Rox::setup", [
                'exception' => $ex
            ]);
            throw new RuntimeException("Rox::setup failed. see innerException", $ex);
        }
    }

    /**
     * @param string $namespace
     * @param object $roxContainer
     */
    public static function register($namespace, $roxContainer)
    {
        self::getCore()->register($namespace, $roxContainer);
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
        try {
            self::getCore()->fetch();
        } catch (Exception $ex) {
            self::getLog()->error("Failed in Rox::fetch", [
                'exception' => $ex
            ]);
        }
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
     * @return DynamicApiInterface
     */
    public static function dynamicApi()
    {
        return self::getCore()->dynamicApi(new ServerEntitiesProvider());
    }
}
