<?php

namespace Rox\Core\Roxx;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Context\ContextBuilder;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Utils\DotNetCompat;
use Rox\Core\Utils\TimeUtils;

class Parser implements ParserInterface
{
    /**
     * @var LoggerInterface
     */
    private $_log;

    /**
     * @var array $_operatorsMap
     */
    private $_operatorsMap = [];

    /**
     * Parser constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->_log = LoggerFactory::getInstance()->createLogger(self::class);
        $this->_setBasicOperators();
    }

    /**
     * @param string $name
     * @param callable $operation
     */
    public function addOperator($name, $operation)
    {
        $this->_operatorsMap[$name] = $operation;
    }

    private function _setBasicOperators()
    {
        $this->addOperator("isUndefined",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $op1 = $stack->pop();
                if (!($op1 instanceof TokenType)) {
                    $stack->push(false);
                    return;
                }
                $stack->push($op1 == TokenType::getUndefined());
            });

        $this->addOperator("now", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $stack->push(TimeUtils::currentTimeMillis());
        });

        $this->addOperator("and",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {

                $op1 = $stack->pop();
                $op2 = $stack->pop();

                if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                    $op1 = false;
                }

                if (!is_bool($op2) && $op2 === TokenType::getUndefined()) {
                    $op2 = false;
                }

                $stack->push((boolean)$op1 && (boolean)$op2);
            });

        $this->addOperator("or",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $op1 = $stack->pop();
                $op2 = $stack->pop();

                if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                    $op1 = false;
                }

                if (!is_bool($op2) && $op2 === TokenType::getUndefined()) {
                    $op2 = false;
                }

                $stack->push((boolean)$op1 || (boolean)$op2);
            });

        $this->addOperator("ne", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                $op1 = false;
            }

            if (!is_bool($op2) && $op2 === TokenType::getUndefined()) {
                $op2 = false;
            }

            $stack->push($op1 !== $op2);
        });

        $this->addOperator("eq", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                $op1 = false;
            }

            if (!is_bool($op2) && $op2 === TokenType::getUndefined()) {
                $op2 = false;
            }

            if (DotNetCompat::isNumericStrict($op1)) {
                $op1 = (float)$op1; // cast int to float for comparison
            }

            if (DotNetCompat::isNumericStrict($op2)) {
                $op2 = (float)$op2; // cast int to float for comparison
            }

            $stack->push($op1 === $op2);
        });

        $this->addOperator("not",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $op1 = $stack->pop();
                if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                    $op1 = false;
                }
                $stack->push(!(boolean)$op1);
            });

        $this->addOperator("ifThen", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $conditionExpression = (boolean)$stack->pop();
            $trueExpression = $stack->pop();
            $falseExpression = $stack->pop();
            $stack->push($conditionExpression ? $trueExpression : $falseExpression);
        });

        $this->addOperator("inArray", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            if (!is_array($op2)) {
                $stack->push(false);
                return;
            }

            $stack->push(!!array_filter($op2, function ($e) use ($op1) {
                return $e === $op1;
            }));
        });

        $this->addOperator("md5", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            if (!is_string($op1)) {
                $stack->push(TokenType::getUndefined());
                return;
            }
            $stack->push(md5($op1));
        });

        $this->addOperator("concat", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();
            if (!is_string($op1) || !is_string($op2)) {
                $stack->push(TokenType::getUndefined());
                return;
            }

            $stack->push($op1 . $op2);
        });

        $this->addOperator("b64d", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            if (!is_string($op1)) {
                $stack->push(TokenType::getUndefined());
                return;
            }

            $stack->push(base64_decode($op1));
        });

        (new ValueCompareExtensions($this))->extend();
        (new RegularExpressionExtensions($this))->extend();
    }

    /**
     * @param string $expression
     * @param ContextInterface $context
     * @param EvaluationContext $evaluationContext
     * @return EvaluationResult
     */
    public function evaluateExpression($expression, $context = null, $evaluationContext = null)
    {
        if ($context == null) {
            $context = (new ContextBuilder())->build(); // Don't pass nulls anywhere, it's a bad practice.
        }

        $stack = new CoreStack();
        $tokens = (new TokenizedExpression($expression, array_keys($this->_operatorsMap)))->getTokens();
        $result = null;

        $reverseTokens = array_reverse($tokens);

        try {
            foreach ($reverseTokens as $token) {
                $node = $token;

                if ($node->getType() == Node::TYPE_RAND) {
                    $stack->push($node->getValue());
                } else if ($node->getType() == Node::TYPE_RATOR) {
                    $key = (string)$node->getValue();
                    if (array_key_exists($key, $this->_operatorsMap)) {
                        $this->_operatorsMap[$key]($this, $stack, $context, $evaluationContext);
                    }
                } else {
                    return new EvaluationResult($result, $context);
                }
            }

            $result = $stack->pop();

        } catch (Exception $exception) {

            $this->_log->warning("Roxx Exception: Failed evaluate expression ${expression}", [
                'exception' => $exception
            ]);
        }

        return new EvaluationResult($result, $context);
    }
}
