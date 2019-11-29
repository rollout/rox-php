<?php

namespace Rox\Core\Roxx;

class TokenType
{
    /**
     * @var string $_text
     */
    private $_text;

    /**
     * @var string $_pattern
     */
    private $_pattern;

    /**
     * TokenType constructor.
     * @param string $text
     * @param string $pattern
     */
    public function __construct($text, $pattern)
    {
        $this->_text = $text;
        $this->_pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_text;
    }

    /**
     * @return TokenType
     */
    public static function getNotAType()
    {
        if (self::$_notAType == null) {
            self::$_notAType = new TokenType("NOT_A_TYPE", "");
        }
        return self::$_notAType;
    }

    /**
     * @return TokenType
     */
    public static function getString()
    {
        if (self::$_string == null) {
            self::$_string = new TokenType(Symbols::RoxxStringType, "/\"((\\\\.)|[^\\\\\\\\\"])*\"/");
        }
        return self::$_string;
    }

    /**
     * @return TokenType
     */
    public static function getNumber()
    {
        if (self::$_number == null) {
            self::$_number = new TokenType(Symbols::RoxxNumberType, "/[\\-]{0,1}\\d+[\\.]\\d+|[\\-]{0,1}\\d+/");
        }
        return self::$_number;
    }

    /**
     * @return TokenType
     */
    public static function getBoolean()
    {
        if (self::$_boolean == null) {
            self::$_boolean = new TokenType(Symbols::RoxxBoolType, sprintf("/%s|%s/", Symbols::RoxxTrue, Symbols::RoxxFalse));
        }
        return self::$_boolean;
    }

    /**
     * @return TokenType
     */
    public static function getUndefined()
    {
        if (self::$_undefined == null) {
            self::$_undefined = new TokenType(Symbols::RoxxUndefinedType, sprintf("/%s/", Symbols::RoxxUndefined));
        }
        return self::$_undefined;
    }

    /**
     * @param string $token
     * @return TokenType
     */
    public static function fromToken($token)
    {
        if ($token != null) {
            $testedToken = strtolower($token);
            foreach ([self::getString(), self::getNumber(), self::getBoolean(), self::getUndefined()] as $tokenType) {
                if (preg_match($tokenType->getPattern(), $testedToken)) {
                    return $tokenType;
                }
            }
        }
        return self::$_notAType;
    }

    /**
     * @var TokenType $_notAType
     */
    private static $_notAType;

    /**
     * @var TokenType $_string
     */
    private static $_string;

    /**
     * @var TokenType $_number
     */
    private static $_number;

    /**
     * @var TokenType $_boolean
     */
    private static $_boolean;

    /**
     * @var TokenType $_undefined
     */
    private static $_undefined;
}
