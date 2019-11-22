<?php

namespace Rox\Core\CustomProperties;

final class CustomPropertyTypes
{
    /**
     * @var CustomPropertyType $_string
     */
    private $_string;

    /**
     * @var CustomPropertyType $_bool
     */
    private $_bool;

    /**
     * @var CustomPropertyType $_int
     */
    private $_int;

    /**
     * @var CustomPropertyType $_double
     */
    private $_double;

    /**
     * @var CustomPropertyType $_semver
     */
    private $_semver;

    /**
     * @var CustomPropertyTypes $_instance
     */
    private static $_instance;

    /**
     * CustomPropertyTypes constructor.
     */
    public function __construct()
    {
        $this->_string = new CustomPropertyType("string", "String");
        $this->_bool = new CustomPropertyType("bool", "Boolean");
        $this->_int = new CustomPropertyType("int", "Number");
        $this->_double = new CustomPropertyType("double", "Number");
        $this->_semver = new CustomPropertyType("semver", "Semver");
    }

    /**
     * @return CustomPropertyType
     */
    public function getString()
    {
        return $this->_string;
    }

    /**
     * @return CustomPropertyType
     */
    public function getBool()
    {
        return $this->_bool;
    }

    /**
     * @return CustomPropertyType
     */
    public function getInt()
    {
        return $this->_int;
    }

    /**
     * @return CustomPropertyType
     */
    public function getDouble()
    {
        return $this->_double;
    }

    /**
     * @return CustomPropertyType
     */
    public function getSemver()
    {
        return $this->_semver;
    }

    /**
     * @return CustomPropertyTypes
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new CustomPropertyTypes();
        }
        return self::$_instance;
    }
}
