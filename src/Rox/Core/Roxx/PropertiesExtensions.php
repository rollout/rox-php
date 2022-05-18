<?php

namespace Rox\Core\Roxx;

use Exception;
use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\DynamicPropertiesInterface;
use Rox\Core\ErrorHandling\ExceptionTrigger;
use Rox\Core\ErrorHandling\UserspaceHandlerException;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;

class PropertiesExtensions
{
    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var CustomPropertyRepositoryInterface $_propertiesRepository
     */
    private $_propertiesRepository;

    /**
     * @var DynamicPropertiesInterface
     */
    private $_dynamicProperties;

    /**
     * PropertiesExtensions constructor.
     * @param ParserInterface $parser
     * @param CustomPropertyRepositoryInterface $propertiesRepository
     * @param DynamicPropertiesInterface $dynamicProperties
     */
    public function __construct(
        ParserInterface                   $parser,
        CustomPropertyRepositoryInterface $propertiesRepository,
        DynamicPropertiesInterface        $dynamicProperties)
    {
        $this->_parser = $parser;
        $this->_propertiesRepository = $propertiesRepository;
        $this->_dynamicProperties = $dynamicProperties;
    }

    public function extend()
    {
        $this->_parser->addOperator("property",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
                $propName = (string)$stack->pop();
                $property = $this->_propertiesRepository->getCustomProperty($propName);

                if ($property == null) {
                    $dynamicPropertiesRule = $this->_dynamicProperties->getDynamicPropertiesRule();
                    if ($dynamicPropertiesRule != null) {
                        if ($this->_dynamicProperties->isDefault()) {
                            // this is our implementation, if there's an exception, shouldn't throw to user error handler
                            $value = $dynamicPropertiesRule($propName, $context);
                        } else {
                            try {
                                $value = $dynamicPropertiesRule($propName, $context);
                            } catch (Exception $ex) {
                                // throwing exception, so the whole evaluation will exit, and default will be applied
                                throw new UserspaceHandlerException($dynamicPropertiesRule, ExceptionTrigger::DynamicPropertiesRule, $ex);
                            }
                        }
                        if ($value != null) {
                            if (is_string($value) || is_bool($value)) {
                                $stack->push($value);
                                return;
                            } else if (is_int($value) || is_double($value)) {
                                $stack->push((double)($value));
                                return;
                            }
                        }
                    }
                    $stack->push(TokenType::getUndefined());
                    return;
                }

                $propValue = $property->getValue();
                try {
                    $value = $propValue($context);
                } catch (Exception $ex) {
                    throw new UserspaceHandlerException($propValue,
                        ExceptionTrigger::CustomPropertyGenerator, $ex);
                }
                if ($value !== null) {
                    $stack->push($value);
                    return;
                }

                $stack->push(TokenType::getUndefined());
            });
    }
}
