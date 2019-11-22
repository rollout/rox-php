<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;

function is_numeric_strict($val)
{
    return is_int($val) || is_float($val);
}

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

            if (!(is_numeric_strict($op1)) || !(is_numeric_strict($op2))) {
                $stack->push(false);
                return;
            }

            $decimal1 = (float)$op1;
            $decimal2 = (float)$op2;

            $stack->push($decimal1 < $decimal2);
        });

        $this->_parser->addOperator("lte", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_numeric_strict($op1)) || !(is_numeric_strict($op2))) {
                $stack->push(false);
                return;
            }

            $decimal1 = (float)$op1;
            $decimal2 = (float)$op2;

            $stack->push($decimal1 <= $decimal2);
        });

        $this->_parser->addOperator("gt", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_numeric_strict($op1)) || !(is_numeric_strict($op2))) {
                $stack->push(false);
                return;
            }

            $decimal1 = (float)$op1;
            $decimal2 = (float)$op2;

            $stack->push($decimal1 > $decimal2);
        });

        $this->_parser->addOperator("gte", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!(is_numeric_strict($op1)) || !(is_numeric_strict($op2))) {
                $stack->push(false);
                return;
            }

            $decimal1 = (float)$op1;
            $decimal2 = (float)$op2;

            $stack->push($decimal1 >= $decimal2);
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
