<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;

class RegularExpressionExtensions
{
    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * RegularExpressionExtensions constructor.
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser)
    {
        $this->_parser = $parser;
    }

    public function extend()
    {
        $this->_parser->addOperator("match",
            function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {

                $op1 = $stack->pop();
                $op2 = $stack->pop();
                $op3 = $stack->pop();

                if (!(is_string($op1))
                    || !(is_string($op2))
                    || !(is_string($op3))) {

                    $stack->push(false);
                    return;
                }

                $str = (string)$op1;
                $pattern = (string)$op2;
                $flags = (string)$op3;

                $filteredFlags = "";

                for ($i = 0; $i < strlen($flags); $i++) {
                    $flag = $flags[$i];
                    if (($flag == 'i') || ($flag == 'x') || ($flag == 'm') || ($flag == 's')) {
                        $filteredFlags .= $flag;
                    }

                    if ($flag == 'n') {
                        // FIXME: use some logging framework here?
                        error_log("Regexp flag ${flag} is not supported.");
                    }
                }

                $stack->push(preg_match("/${pattern}/${filteredFlags}", $str) === 1);
            });
    }
}
