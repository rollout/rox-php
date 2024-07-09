<?php

namespace Rox\Core\Roxx;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Context\ContextBuilder;
use Rox\Core\Context\ContextInterface;
use Rox\Core\ErrorHandling\UserspaceHandlerException;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Utils\NumericUtils;
use Rox\Core\Utils\TimeUtils;
use DateTime;

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
     * @var UserspaceUnhandledErrorInvokerInterface $_userUnhandledErrorInvoker
     */
    private $_userUnhandledErrorInvoker;

    /**
     * @var ContextInterface $_globalContext
     */
    private $_globalContext;

    /**
     * Parser constructor.
     * @param UserspaceUnhandledErrorInvokerInterface|null $userUnhandledErrorInvoker
     */
    public function __construct($userUnhandledErrorInvoker)
    {
        $this->_userUnhandledErrorInvoker = $userUnhandledErrorInvoker;
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
        $this->addOperator(
            "isUndefined",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $op1 = $stack->pop();
                if (!($op1 instanceof TokenType)) {
                    $stack->push(false);
                    return;
                }
                $stack->push($op1 == TokenType::getUndefined());
            }
        );

        $this->addOperator("now", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $stack->push(TimeUtils::currentTimeMillis());
        });

        $this->addOperator(
            "and",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {

                $op1 = $stack->pop();
                $op2 = $stack->pop();

                if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                    $op1 = false;
                }

                if (!is_bool($op2) && $op2 === TokenType::getUndefined()) {
                    $op2 = false;
                }

                $stack->push((boolean) $op1 && (boolean) $op2);
            }
        );

        $this->addOperator(
            "or",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $op1 = $stack->pop();
                $op2 = $stack->pop();

                if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                    $op1 = false;
                }

                if (!is_bool($op2) && $op2 === TokenType::getUndefined()) {
                    $op2 = false;
                }

                $stack->push((boolean) $op1 || (boolean) $op2);
            }
        );

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

        $this->addOperator("numne", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            $decimal1 = 0;
            $decimal2 = 0;
            $result = false;

            if (
                NumericUtils::parseNumber($op1, $decimal1) &&
                NumericUtils::parseNumber($op2, $decimal2)
            ) {
                $result = !NumericUtils::numbersEqual($decimal1, $decimal2);
            }

            $stack->push($result);
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

            if (NumericUtils::isNumericStrict($op1) && NumericUtils::isNumericStrict($op2)) {
                $stack->push(NumericUtils::numbersEqual($op1, $op2));
                return;
            }

            $stack->push($op1 === $op2);
        });

        $this->addOperator("numeq", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = $stack->pop();
            $op2 = $stack->pop();

            $decimal1 = 0;
            $decimal2 = 0;
            $result = false;

            if (
                NumericUtils::parseNumber($op1, $decimal1) &&
                NumericUtils::parseNumber($op2, $decimal2)
            ) {
                $result = NumericUtils::numbersEqual($decimal1, $decimal2);
            }

            $stack->push($result);
        });

        $this->addOperator(
            "not",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $op1 = $stack->pop();
                if (!is_bool($op1) && $op1 === TokenType::getUndefined()) {
                    $op1 = false;
                }
                $stack->push(!(boolean) $op1);
            }
        );

        $this->addOperator("ifThen", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $conditionExpression = (boolean) $stack->pop();
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

        $this->addOperator("tsToNum", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $op1 = (object) $stack->pop();

            if ($op1 instanceof DateTime) {
                // getTimestamp return the number of Epoch seconds, no need to divide by 1000
                $stack->push($op1->getTimestamp());
                return;
            }

            $stack->push(TokenType::getUndefined());

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
                    $key = (string) $node->getValue();
                    if (array_key_exists($key, $this->_operatorsMap)) {
                        $this->_operatorsMap[$key]($this, $stack, $context, $evaluationContext);
                    }
                } else {
                    return new EvaluationResult($result, $context);
                }
            }

            $result = $stack->pop();

        } catch (UserspaceHandlerException $ex) {

            $this->_log->warning("Roxx Exception: Failed evaluate expression, user unhandled expression {$ex->getMessage()}", [
                'exception' => $ex
            ]);

            if ($this->_userUnhandledErrorInvoker) {
                $this->_userUnhandledErrorInvoker->invoke(
                    $ex->getExceptionSource(),
                    $ex->getExceptionTrigger(),
                    $ex->getException()
                );
            }

        } catch (Exception $exception) {

            $this->_log->warning("Roxx Exception: Failed evaluate expression {$expression}", [
                'exception' => $exception
            ]);
        }

        return new EvaluationResult($result, $context);
    }

    function setGlobalContext($context)
    {
        $this->_globalContext = $context;
    }

    function getGlobalContext()
    {
        return $this->_globalContext;
    }
}
