<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;
use Rox\Core\Utils\DotNetCompat;
use Rox\Core\Utils\NumericUtils;

class ValueCompareExtensions
{
    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    public function __construct($parser)
    {
        $this->_parser = $parser;
    }

    public function extend()
    {
        $this->_parser->addOperator("lt", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            $decimal1 = 0;
            $decimal2 = 0;
            $result = false;

            if (NumericUtils::parseNumber($op1, $decimal1) &&
                NumericUtils::parseNumber($op2, $decimal2)) {
                $result = $decimal1 < $decimal2;
            }

            $stack->push($result);
        });

        $this->_parser->addOperator("lte", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            $decimal1 = 0;
            $decimal2 = 0;
            $result = false;

            if (NumericUtils::parseNumber($op1, $decimal1) &&
                NumericUtils::parseNumber($op2, $decimal2)) {
                $result = $decimal1 < $decimal2 || NumericUtils::numbersEqual($decimal1, $decimal2);
            }

            $stack->push($result);
        });

        $this->_parser->addOperator("gt", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            $decimal1 = 0;
            $decimal2 = 0;
            $result = false;

            if (NumericUtils::parseNumber($op1, $decimal1) &&
                NumericUtils::parseNumber($op2, $decimal2)) {
                $result = $decimal1 > $decimal2;
            }

            $stack->push($result);
        });

        $this->_parser->addOperator("gte", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            $decimal1 = 0;
            $decimal2 = 0;
            $result = false;

            if (NumericUtils::parseNumber($op1, $decimal1) &&
                NumericUtils::parseNumber($op2, $decimal2)) {
                $result = $decimal1 > $decimal2 || NumericUtils::numbersEqual($decimal1, $decimal2);
            }

            $stack->push($result);
        });

        $this->_parser->addOperator("semverNe", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_string($op1)) || !(is_string($op2))) {
                $stack->push(false);
                return;
            }

            $stack->push(version_compare($op1, $op2) != 0);
        });

        $this->_parser->addOperator("semverEq", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_string($op1)) || !(is_string($op2))) {
                $stack->push(false);
                return;
            }

            $stack->push(version_compare($op1, $op2) == 0);
        });

        $this->_parser->addOperator("semverLt", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_string($op1)) || !(is_string($op2))) {
                $stack->push(false);
                return;
            }

            $stack->push(version_compare($op1, $op2) < 0);
        });

        $this->_parser->addOperator("semverLte", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_string($op1)) || !(is_string($op2))) {
                $stack->push(false);
                return;
            }

            $stack->push(version_compare($op1, $op2) <= 0);
        });

        $this->_parser->addOperator("semverGt", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_string($op1)) || !(is_string($op2))) {
                $stack->push(false);
                return;
            }

            $stack->push(version_compare($op1, $op2) > 0);
        });

        $this->_parser->addOperator("semverGte", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_string($op1)) || !(is_string($op2))) {
                $stack->push(false);
                return;
            }

            $stack->push(version_compare($op1, $op2) >= 0);
        });
    }
}
