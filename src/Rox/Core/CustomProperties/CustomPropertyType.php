<?php

namespace Rox\Core\CustomProperties;

class CustomPropertyType
{
    /**
     * @var string $_type
     */
    private $_type;

    /**
     * @var string $_externalType
     */
    private $_externalType;

    /**
     * CustomPropertyType constructor.
     * @param string $type
     * @param string $externalType
     */
    public function __construct($type, $externalType)
    {
        $this->_type = $type;
        $this->_externalType = $externalType;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return string
     */
    public function getExternalType()
    {
        return $this->_externalType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_type;
    }

    /**
     * @return CustomPropertyType
     */
    public static function getString()
    {
        if (self::$_string == null) {
            self::$_string = new CustomPropertyType("string", "String");
        }
        return self::$_string;
    }

    /**
     * @return CustomPropertyType
     */
    public static function getBool()
    {
        if (self::$_bool == null) {
            self::$_bool = new CustomPropertyType("bool", "Boolean");
        }
        return self::$_bool;
    }

    /**
     * @return CustomPropertyType
     */
    public static function getInt()
    {
        if (self::$_int == null) {
            self::$_int = new CustomPropertyType("int", "Number");
        }
        return self::$_int;
    }

    /**
     * @return CustomPropertyType
     */
    public static function getDouble()
    {
        if (self::$_double == null) {
            self::$_double = new CustomPropertyType("double", "Number");
        }
        return self::$_double;
    }

    /**
     * @return CustomPropertyType
     */
    public static function getSemver()
    {
        if (self::$_semver == null) {
            self::$_semver = new CustomPropertyType("semver", "Semver");
        }
        return self::$_semver;
    }

    /**
     * @var CustomPropertyType $_string
     */
    private static $_string;

    /**
     * @var CustomPropertyType $_bool
     */
    private static $_bool;

    /**
     * @var CustomPropertyType $_int
     */
    private static $_int;

    /**
     * @var CustomPropertyType $_double
     */
    private static $_double;

    /**
     * @var CustomPropertyType $_semver
     */
    private static $_semver;
}
