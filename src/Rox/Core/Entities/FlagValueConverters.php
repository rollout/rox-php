<?php

namespace Rox\Core\Entities;

use RuntimeException;

final class FlagValueConverters
{
    /**
     * @var FlagValueConverters $_instance
     */
    private static $_instance = null;

    /**
     * @var FlagValueConverter $_int
     */
    private $_int;

    /**
     * @var FlagValueConverter $_double
     */
    private $_double;

    /**
     * @var FlagValueConverter $_bool
     */
    private $_bool;

    /**
     * @var FlagValueConverter $_string
     */
    private $_string;

    /**
     * @return FlagValueConverters
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new FlagValueConverters();
        }
        return self::$_instance;
    }

    /**
     * @return FlagValueConverter
     */
    public function getInt()
    {
        return $this->_int;
    }

    /**
     * @return FlagValueConverter
     */
    public function getDouble()
    {
        return $this->_double;
    }

    /**
     * @return FlagValueConverter
     */
    public function getBool()
    {
        return $this->_bool;
    }

    /**
     * @return FlagValueConverter
     */
    public function getString()
    {
        return $this->_string;
    }

    private function __construct()
    {
        $this->_bool = new BoolFlagValueConverter();
        $this->_string = new StringFlagValueConverter();
        $this->_int = new IntFlagValueConverter();
        $this->_double = new DoubleFlagValueConverter();
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new RuntimeException("Cannot deserialize singleton");
    }
}
