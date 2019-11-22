<?php

namespace Rox\Core\Roxx;

final class TokenTypes
{
    /**
     * @var TokenType $_notAType
     */
    private $_notAType;

    /**
     * @var TokenType $_string
     */
    private $_string;

    /**
     * @var TokenType $_number
     */
    private $_number;

    /**
     * @var TokenType $_boolean
     */
    private $_boolean;

    /**
     * @var TokenType $_undefined
     */
    private $_undefined;

    /**
     * TokenTypes constructor.
     */
    public function __construct()
    {
        $this->_notAType = new TokenType("NOT_A_TYPE", "");
        $this->_string = new TokenType(Symbols::RoxxStringType, "/\"((\\\\.)|[^\\\\\\\\\"])*\"/");
        $this->_number = new TokenType(Symbols::RoxxNumberType, "/[\\-]{0,1}\\d+[\\.]\\d+|[\\-]{0,1}\\d+/");
        $this->_boolean = new TokenType(Symbols::RoxxBoolType, sprintf("/%s|%s/", Symbols::RoxxTrue, Symbols::RoxxFalse));
        $this->_undefined = new TokenType(Symbols::RoxxUndefinedType, sprintf("/%s/", Symbols::RoxxUndefined));
    }

    /**
     * @return TokenType
     */
    public function getNotAType()
    {
        return $this->_notAType;
    }

    /**
     * @return TokenType
     */
    public function getString()
    {
        return $this->_string;
    }

    /**
     * @return TokenType
     */
    public function getNumber()
    {
        return $this->_number;
    }

    /**
     * @return TokenType
     */
    public function getBoolean()
    {
        return $this->_boolean;
    }

    /**
     * @return TokenType
     */
    public function getUndefined()
    {
        return $this->_undefined;
    }

    /**
     * @param string $token
     * @return TokenType
     */
    public function fromToken($token)
    {
        if ($token != null) {
            $testedToken = strtolower($token);
            foreach ([$this->_string, $this->_number, $this->_boolean, $this->_undefined] as $tokenType) {
                if (preg_match($tokenType->getPattern(), $testedToken)) {
                    return $tokenType;
                }
            }
        }
        return $this->_notAType;
    }

    private static $_instance = null;

    /**
     * @return TokenTypes
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new TokenTypes();
        }
        return self::$_instance;
    }
}