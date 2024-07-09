<?php

namespace Rox\Core\Utils;

/**
 * 
 * Utility functions for API keys
 *
 */
class ApiKeyHelpers
{
    const UUID_API_KEY_PATTERN = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i";
    const MONGO_API_KEY_PATTERN = "/^[a-f\\d]{24}$/i";

    /**
     * @param string $apiKey
     * @return bool
     */
    public static function isValidKey($apiKey)
    {
        return self::isCBPApiKey($apiKey) || self::isRolloutApiKey($apiKey);
    }

    /**
     * @param string $apiKey
     * @return bool
     */
    public static function isCBPApiKey($apiKey)
    {
        return preg_match(self::UUID_API_KEY_PATTERN, $apiKey);
        ;
    }

    /**
     * @param string $apiKey
     * @return bool
     */
    public static function isRolloutApiKey($apiKey)
    {
        return preg_match(self::MONGO_API_KEY_PATTERN, $apiKey);
    }
}
