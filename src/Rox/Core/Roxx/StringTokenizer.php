<?php

namespace Rox\Core\Roxx;

/**
 * <p>Class StringTokenizer
 *
 * <p>Implements a StringTools.StringTokenizer class for splitting a string
 * into substrings using a set of delimiters.
 *
 * <p>PHP version of the java.util.StringTokenizer class.
 *
 * @package Rox\Core\Roxx
 */
class StringTokenizer
{
    /**
     * @var int $_currentPosition
     */
    private $_currentPosition;

    /**
     * @var int $_newPosition
     */
    private $_newPosition;

    /**
     * @var int $_maxPosition
     */
    private $_maxPosition;

    /**
     * @var bool $_retDelims
     */
    private $_retDelims;

    /**
     * @var bool $_delimsChanged
     */
    private $_delimsChanged;

    /**
     * @var string $_str
     */
    private $_str;

    /**
     * @var string $_delim
     */
    private $_delimiters;

    /**
     * Stores the value of the delimiter character with the
     * highest value. It is used to optimize the detection of delimiter
     * characters.
     *
     * It is unlikely to provide any optimization benefit in the
     * hasSurrogates case because most string characters will be
     * smaller than the limit, but we keep it so that the two code
     * paths remain similar.
     *
     * @var int $_maxDelimCodePoint
     */
    private $_maxDelimCodePoint;

    /**
     * If delimiters include any surrogates (including surrogate
     * pairs), hasSurrogates is true and the tokenizer uses the
     * different code path. This is because String.indexOf(int)
     * doesn't handle unpaired surrogates as a single character.
     *
     * @var bool $_hasSurrogates
     */
    private $_hasSurrogates = false;

    /**
     * When hasSurrogates is true, delimiters are converted to code
     * points and isDelimiter(int) is used to determine if the given
     * codepoint is a delimiter.
     *
     * @var int[] $_delimiterCodePoints
     */
    private $_delimiterCodePoints;

    /**
     * StringTokenizer constructor.
     * @param string $str
     * @param string $delim
     * @param bool $returnDelims
     */
    public function __construct($str, $delim, $returnDelims)
    {
        $this->_str = $str;
        $this->_delimiters = $delim;
        $this->_retDelims = $returnDelims;

        $this->_currentPosition = 0;
        $this->_newPosition = -1;
        $this->_delimsChanged = false;
        $this->_maxPosition = strlen($str);
        $this->_setMaxDelimCodePoint();
    }

    private function _setMaxDelimCodePoint()
    {
        if ($this->_delimiters == null) {
            $this->_maxDelimCodePoint = 0;
            return;
        }

        $m = 0;
        $c = null;
        $count = 0;
        for ($i = 0; $i < strlen($this->_delimiters); $i += self::charCount($c)) {
            $c = $this->_delimiters[$i];
            if (self::isSurrogate($c)) {
                $c = $this->_delimiters[$i];
                $this->_hasSurrogates = true;
            }
            if ($m < $c)
                $m = $c;
            $count++;
        }
        $this->_maxDelimCodePoint = $m;

        if ($this->_hasSurrogates) {
            $this->_delimiterCodePoints = [$count];
            for ($i = 0, $j = 0; $i < $count; $i++, $j += self::charCount($c)) {
                $c = $this->_delimiters[$j];
                $this->_delimiterCodePoints[$i] = $c;
            }
        }
    }

    /**
     * @param string $c
     * @return bool
     */
    private static function isSurrogate($c)
    {
        $min = "\x{d800}";
        $max = "\x{dfff}";
        return ord($c) - ord($min) <= ord($max) - ord($min);
    }

    /**
     * @param int $codePoint
     * @return int
     */
    private static function charCount($codePoint)
    {
        return ord($codePoint) >= self::MIN_SUPPLEMENTARY_CODE_POINT ? 2 : 1;
    }

    /**
     * Skips delimiters starting from the specified position. If retDelims
     * is false, returns the index of the first non-delimiter character at or
     * after startPos. If retDelims is true, startPos is returned.
     *
     * @param int $startPos
     * @return int
     */
    private function _skipDelimiters($startPos)
    {
        $position = $startPos;
        while (!$this->_retDelims && $position < $this->_maxPosition) {
            if (!$this->_hasSurrogates) {
                $c = $this->_str[$position];
                if (($c > $this->_maxDelimCodePoint) || (strpos($this->_delimiters, $c) === false))
                    break;
                $position++;
            } else {
                $c = $this->_str[$position];
                if (($c > $this->_maxDelimCodePoint) || !$this->_isDelimiter($c)) {
                    break;
                }
                $position += self::charCount($c);
            }
        }
        return $position;
    }

    /**
     * Skips ahead from startPos and returns the index of the next delimiter
     * character encountered, or maxPosition if no such delimiter is found.
     *
     * @param int $startPos
     * @return int
     */
    private function _scanToken($startPos)
    {
        $position = $startPos;
        while ($position < $this->_maxPosition) {
            if (!$this->_hasSurrogates) {
                $c = $this->_str[$position];
                $strpos = strpos($this->_delimiters, $c);
                if (($c <= $this->_maxDelimCodePoint) && ($strpos !== false && $strpos >= 0))
                    break;
                $position++;
            } else {
                $c = $this->_str[$position];
                if (($c <= $this->_maxDelimCodePoint) && $this->_isDelimiter($c))
                    break;
                $position += self::charCount($c);
            }
        }
        if ($this->_retDelims && ($startPos == $position)) {
            if (!$this->_hasSurrogates) {
                $c = $this->_str[$position];
                $strpos = strpos($this->_delimiters, $c);
                if (($c <= $this->_maxDelimCodePoint) && ($strpos !== false && $strpos >= 0))
                    $position++;
            } else {
                $c = $this->_str[$position];
                if (($c <= $this->_maxDelimCodePoint) && $this->_isDelimiter($c))
                    $position += self::charCount($c);
            }
        }
        return $position;
    }

    /**
     * @param int $codePoint
     * @return bool
     */
    private function _isDelimiter($codePoint)
    {
        for ($i = 0; $i < count($this->_delimiterCodePoints); $i++) {
            if ($this->_delimiterCodePoints[$i] == $codePoint) {
                return true;
            }
        }
        return false;
    }

    /**
     * Tests if there are more tokens available from this tokenizer's string.
     * If this method returns <tt>true</tt>, then a subsequent call to
     * <tt>NextToken</tt> with no argument will successfully return a token.
     *
     * @return bool <code>true</code> if and only if there is at least one token
     *          in the string after the current position; <code>false</code>
     *          otherwise.
     */
    public function hasMoreTokens()
    {
        /*
         * Temporarily store this position and use it in the following
         * NextToken() method only if the delimiters haven't been changed in
         * that NextToken() invocation.
         */
        $this->_newPosition = $this->_skipDelimiters($this->_currentPosition);
        return ($this->_newPosition < $this->_maxPosition);
    }

    /**
     * Returns the next token from this string tokenizer.
     *
     * @return string The next token from this string tokenizer or <code>null</code>
     * if there are no more tokens in this tokenizer's string.
     */
    public function nextToken()
    {
        /*
         * If next position already computed in hasMoreElements() and
         * delimiters have changed between the computation and this invocation,
         * then use the computed value.
         */

        $this->_currentPosition = ($this->_newPosition >= 0 && !$this->_delimsChanged) ?
            $this->_newPosition : $this->_skipDelimiters($this->_currentPosition);

        /* Reset these anyway */
        $this->_delimsChanged = false;
        $this->_newPosition = -1;

        if ($this->_currentPosition >= $this->_maxPosition)
            return null;
        $start = $this->_currentPosition;
        $this->_currentPosition = $this->_scanToken($this->_currentPosition);
        return substr($this->_str, $start, $this->_currentPosition - $start);
    }

    /**
     * Returns the next token in this string tokenizer's string. First,
     * the set of characters considered to be delimiters by this
     * <tt>StringTokenizer</tt> object is changed to be the characters in
     * the string <tt>delim</tt>. Then the next token in the string
     * after the current position is returned. The current position is
     * advanced beyond the recognized token.  The new delimiter set
     * remains the default after this call.
     *
     * @param string $delim The new delimiters.
     * @return string The next token, after switching to the new delimiter set.
     */
    public function nextTokenByDelim($delim)
    {
        $this->_delimiters = $delim;

        /* delimiter string specified, so set the appropriate flag. */
        $this->_delimsChanged = true;

        $this->_setMaxDelimCodePoint();
        return $this->nextToken();
    }

    /**
     * Calculates the number of times that this tokenizer's
     * <code>NextToken</code> method can be called before it generates an
     * exception. The current position is not advanced.
     *
     * @return int The number of tokens remaining in the string using the current delimiter set.
     */
    public function countTokens()
    {
        $count = 0;
        $currpos = $this->_currentPosition;
        while ($currpos < $this->_maxPosition) {
            $currpos = $this->_skipDelimiters($currpos);
            if ($currpos >= $this->_maxPosition)
                break;
            $currpos = $this->_scanToken($currpos);
            $count++;
        }
        return $count;
    }

    const MIN_SUPPLEMENTARY_CODE_POINT = 0x010000;
}
