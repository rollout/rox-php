<?php

namespace Rox\Core\Consts;

final class PropertyType
{
    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var int $_value
     */
    private $_value;

    /**
     * PropertyType constructor.
     * @param int $_value
     * @param string $_name
     */
    public function __construct($_value, $_name)
    {
        $this->_name = $_name;
        $this->_value = $_value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_name;
    }

    /**
     * @return PropertyType
     */
    public static function getCacheMissRelativeUrl()
    {
        if (self::$cache_miss_relative_url == null) {
            self::$cache_miss_relative_url = new PropertyType(1, "cache_miss_relative_url");
        }
        return self::$cache_miss_relative_url;
    }

    /**
     * @return PropertyType
     */
    public static function getLibVersion()
    {
        if (self::$lib_version == null) {
            self::$lib_version = new PropertyType(4, "lib_version");
        }
        return self::$lib_version;
    }

    /**
     * @return PropertyType
     */
    public static function getRolloutBuild()
    {
        if (self::$rollout_build == null) {
            self::$rollout_build = new PropertyType(5, 'rollout_build');
        }
        return self::$rollout_build;
    }

    /**
     * @return PropertyType
     */
    public static function getApiVersion()
    {
        if (self::$api_version == null) {
            self::$api_version = new PropertyType(6, 'api_version');
        }
        return self::$api_version;
    }

    /**
     * @return PropertyType
     */
    public static function getBuid()
    {
        if (self::$buid == null) {
            self::$buid = new PropertyType(7, 'buid');
        }
        return self::$buid;
    }

    /**
     * @return PropertyType
     */
    public static function getBuidGeneratorsList()
    {
        if (self::$buid_generators_list == null) {
            self::$buid_generators_list = new PropertyType(8, 'buid_generators_list');
        }
        return self::$buid_generators_list;
    }

    /**
     * @return PropertyType
     */
    public static function getAppRelease()
    {
        if (self::$app_release == null) {
            self::$app_release = new PropertyType(10, 'app_release');
        }
        return self::$app_release;
    }

    /**
     * @return PropertyType
     */
    public static function getDistinctId()
    {
        if (self::$distinct_id == null) {
            self::$distinct_id = new PropertyType(11, 'distinct_id');
        }
        return self::$distinct_id;
    }

    /**
     * @return PropertyType
     */
    public static function getAppKey()
    {
        if (self::$app_key == null) {
            self::$app_key = new PropertyType(12, 'app_key');
        }
        return self::$app_key;
    }

    /**
     * @return PropertyType
     */
    public static function getFeatureFlags()
    {
        if (self::$feature_flags == null) {
            self::$feature_flags = new PropertyType(13, 'feature_flags');
        }
        return self::$feature_flags;
    }

    /**
     * @return PropertyType
     */
    public static function getRemoteVariables()
    {
        if (self::$remote_variables == null) {
            self::$remote_variables = new PropertyType(14, 'remote_variables');
        }
        return self::$remote_variables;
    }

    /**
     * @return PropertyType
     */
    public static function getCustomProperties()
    {
        if (self::$custom_properties == null) {
            self::$custom_properties = new PropertyType(15, 'custom_properties');
        }
        return self::$custom_properties;
    }

    /**
     * @return PropertyType
     */
    public static function getPlatform()
    {
        if (self::$platform == null) {
            self::$platform = new PropertyType(16, 'platform');
        }
        return self::$platform;
    }

    /**
     * @return PropertyType
     */
    public static function getDevModeSecret()
    {
        if (self::$dev_mode_secret == null) {
            self::$dev_mode_secret = new PropertyType(17, 'devModeSecret');
        }
        return self::$dev_mode_secret;
    }

    /**
     * @return PropertyType
     */
    public static function getStateMd5()
    {
        if (self::$state_md5 == null) {
            self::$state_md5 = new PropertyType(18, 'state_md5');
        }
        return self::$state_md5;
    }

    /**
     * @return PropertyType
     */
    public static function getFeatureFlagsString()
    {
        if (self::$feature_flags_string == null) {
            self::$feature_flags_string = new PropertyType(19, 'feature_flags_string');
        }
        return self::$feature_flags_string;
    }

    /**
     * @return PropertyType
     */
    public static function getRemoteVariablesString()
    {
        if (self::$remote_variables_string == null) {
            self::$remote_variables_string = new PropertyType(20, 'remote_variables_string');
        }
        return self::$remote_variables_string;
    }

    /**
     * @return PropertyType
     */
    public static function getCustomPropertiesString()
    {
        if (self::$custom_properties_string == null) {
            self::$custom_properties_string = new PropertyType(21, 'custom_properties_string');
        }
        return self::$custom_properties_string;
    }

    /**
     * @return PropertyType
     */
    public static function getDateTime()
    {
        if (self::$datetime_string == null) {
            self::$datetime_string = new PropertyType(21, 'now');
        }
        return self::$datetime_string;
    }

    /**
     * @var PropertyType $cache_miss_relative_url
     */
    private static $cache_miss_relative_url;

    /**
     * @var PropertyType $lib_version
     */
    private static $lib_version;

    /**
     * @var PropertyType $rollout_build
     */
    private static $rollout_build;

    /**
     * @var PropertyType $api_version
     */
    private static $api_version;

    /**
     * @var PropertyType $buid
     */
    private static $buid;

    /**
     * @var PropertyType $buid_generators_list
     */
    private static $buid_generators_list;

    /**
     * @var PropertyType $app_release
     */
    private static $app_release;

    /**
     * @var PropertyType $distinct_id
     */
    private static $distinct_id;

    /**
     * @var PropertyType $app_key
     */
    private static $app_key;

    /**
     * @var PropertyType $feature_flags
     */
    private static $feature_flags;

    /**
     * @var PropertyType $remote_variables
     */
    private static $remote_variables;

    /**
     * @var PropertyType $custom_properties
     */
    private static $custom_properties;

    /**
     * @var PropertyType $platform
     */
    private static $platform;

    /**
     * @var PropertyType $dev_mode_secret
     */
    private static $dev_mode_secret;

    /**
     * @var PropertyType $state_md5
     */
    private static $state_md5;

    /**
     * @var PropertyType $feature_flags_string
     */
    private static $feature_flags_string;

    /**
     * @var PropertyType $remote_variables_string
     */
    private static $remote_variables_string;

    /**
     * @var PropertyType $custom_properties_string
     */
    private static $custom_properties_string;

    /**
     * @var PropertyType $datetime_string
     */
    private static $datetime_string;
}
