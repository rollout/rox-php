<?php

namespace Rox\Core\Roxx;

use RuntimeException;

class TokenizedExpression
{
    private static $_dictStartDelimiter = "{";
    private static $_dictEndDelimiter = "}";
    private static $_arrayStartDelimiter = "[";
    private static $_arrayEndDelimiter = "]";
    private static $_tokenDelimiters = "{}[]():, \t\r\n\"";
    private static $_prePostStringChar = "";
    private static $_stringDelimiter = "\"";
    private static $_escapedQuote = "\\\"";
    private static $_escapedQuotePlaceholder = "\\RO_Q";

    /**
     * @var string $_expression
     */
    private $_expression;

    /**
     * @var string[]
     */
    private $_operators;

    /**
     * @var Node[] $_resultList
     */
    private $_resultList;

    /**
     * @var mixed[] $_arrayAccumulator
     */
    private $_arrayAccumulator;

    /**
     * @var array $_dictAccumulator
     */
    private $_dictAccumulator;

    /**
     * @var string $_dictKey
     */
    private $_dictKey;

    /**
     * TokenizedExpression constructor.
     * @param string $expression
     * @param string[] $operators
     */
    public function __construct($expression, $operators)
    {
        $this->_expression = $expression;
        $this->_operators = $operators;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->_expression;
    }

    /**
     * @return Node[]
     */
    public function getTokens()
    {
        return $this->_tokenize($this->_expression);
    }

    private function _nodeFromCollection($o)
    {
        return new Node(Node::TYPE_RAND, $o);
    }

    private function _nodeFromToken($o)
    {
        if (in_array((string)$o, $this->_operators)) {
            return new Node(Node::TYPE_RATOR, $o);
        } else if (is_string($o)) {
            $s = (string)$o;
            $tokenType = TokenTypes::getInstance()->fromToken($s);
            if ($s == Symbols::RoxxTrue) return new Node(Node::TYPE_RAND, true);
            if ($s == Symbols::RoxxFalse) return new Node(Node::TYPE_RAND, false);
            if ($s == Symbols::RoxxUndefined) return new Node(Node::TYPE_RAND, TokenTypes::getInstance()->getUndefined());
            if ($tokenType === TokenTypes::getInstance()->getString())
                return new Node(Node::TYPE_RAND, substr($s, 1, strlen($s) - 1));
            if ($tokenType === TokenTypes::getInstance()->getNumber()) {
                if (is_numeric($s)) {
                    if (strpos($s, ".") !== false) {
                        return new Node(Node::TYPE_RAND, floatval($s));
                    } else {
                        return new Node(Node::TYPE_RAND, intval($s));
                    }
                } else {
                    throw new RuntimeException(sprintf("Excepted Number, got '%s' (%s)", $s,
                        TokenTypes::getInstance()->fromToken($s)));
                }
            }
        }
        return new Node(Node::TYPE_UNKNOWN, null);
    }

    /**
     * @param Node $node
     */
    private function _pushNode($node)
    {
        if ($this->_dictAccumulator !== null && $this->_dictKey === null) {
            $this->_dictKey = (string)$node->value;
        } else if ($this->_dictAccumulator !== null && $this->_dictKey !== null) {
            if (!isset($this->_dictAccumulator[$this->_dictKey])) {
                $this->_dictAccumulator[$this->_dictKey] = $node->value;
                $this->_dictKey = null;
            }
        } else if ($this->_arrayAccumulator !== null) {
            array_push($this->_arrayAccumulator, $node->value);
        } else {
            array_push($this->_resultList, $node);
        }
    }

    /**
     * @param string $expression
     * @return Node[]
     */
    private function _tokenize($expression)
    {
        $this->_resultList = [];
        $this->_dictAccumulator = null;
        $this->_arrayAccumulator = null;
        $this->_dictKey = null;

        $delimitersToUse = self::$_tokenDelimiters;
        $normalizedExpression = str_replace(self::$_escapedQuote, self::$_escapedQuotePlaceholder, $expression);
        $tokenizer = new StringTokenizer($normalizedExpression, $delimitersToUse, true);

        $prevToken = null;
        $token = null;
        while ($tokenizer->hasMoreTokens()) {
            $prevToken = $token;
            $token = $tokenizer->nextTokenByDelim($delimitersToUse);
            $inString = $delimitersToUse == self::$_stringDelimiter;

            if (!$inString && $token == self::$_dictStartDelimiter) {
                $this->_dictAccumulator = [];
            } else if (!$inString && $token == self::$_dictEndDelimiter) {
                $dictResult = $this->_dictAccumulator;
                $this->_dictAccumulator = null;
                $this->_pushNode($this->_nodeFromCollection($dictResult));
            } else if (!$inString && $token == self::$_arrayStartDelimiter) {
                $this->_arrayAccumulator = [];
            } else if (!$inString && $token == self::$_arrayEndDelimiter) {
                $arrayResult = $this->_arrayAccumulator;
                $this->_arrayAccumulator = null;
                $this->_pushNode($this->_nodeFromCollection($arrayResult));
            } else if ($token == self::$_stringDelimiter) {
                if ($prevToken != null && $prevToken == self::$_stringDelimiter) {
                    $this->_pushNode($this->_nodeFromToken(Symbols::RoxxEmptyString));
                }
                $delimitersToUse = $inString ? self::$_tokenDelimiters : self::$_stringDelimiter;
            } else {
                if ($delimitersToUse == self::$_stringDelimiter) {
                    $this->_pushNode(new Node(Node::TYPE_RAND, str_replace(self::$_escapedQuotePlaceholder, self::$_escapedQuote, $token)));
                } else if (!strpos(self::$_tokenDelimiters, $token) !== false && $token != self::$_prePostStringChar) {
                    $this->_pushNode($this->_nodeFromToken($token));
                }
            }
        }

        return $this->_resultList;
    }
}
