<?php

namespace Rox\Core\Roxx;

use Rox\Core\CustomProperties\CustomPropertyRepository;
use Rox\Core\CustomProperties\DynamicProperties;
use Rox\Core\Repositories\ExperimentRepository;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Repositories\TargetGroupRepository;
use Rox\RoxTestCase;

/**
 * Class ParserTest
 * @package Rox\Core\Roxx
 * @covers Parser
 */
class ParserTests extends RoxTestCase
{
    public function testSimpleTokenization()
    {
        $operators = ["eq", "lt"];

        $tokens = (new TokenizedExpression("eq(false, lt(-123, \"123\"))", $operators))->getTokens();

        $this->assertSame(5, count($tokens));
        $this->assertSame(Node::TYPE_RATOR, $tokens[0]->getType());
        $this->assertSame(false, $tokens[1]->getValue());
        $this->assertSame(-123, $tokens[3]->getValue());
        $this->assertSame("123", $tokens[4]->getValue());
    }

    public function testTokenType()
    {
        $this->assertSame(TokenType::getNumber(), TokenType::fromToken("123"));
        $this->assertSame(TokenType::getNumber(), TokenType::fromToken("-123"));
        $this->assertSame(TokenType::getNumber(), TokenType::fromToken("-123.23"));
        $this->assertSame(TokenType::getNumber(), TokenType::fromToken("123.23"));

        $this->assertNotSame(TokenType::getString(), TokenType::fromToken("-123"));
        $this->assertSame(TokenType::getString(), TokenType::fromToken("\"-123\""));
        $this->assertSame(TokenType::getString(), TokenType::fromToken("\"undefined\""));
        $this->assertNotSame(TokenType::getString(), TokenType::fromToken("undefined"));

        $this->assertSame(TokenType::getBoolean(), TokenType::fromToken("false"));
        $this->assertSame(TokenType::getBoolean(), TokenType::fromToken("true"));
        $this->assertNotSame(TokenType::getBoolean(), TokenType::fromToken("undefined"));

        $this->assertSame(TokenType::getUndefined(), TokenType::fromToken("undefined"));
        $this->assertNotSame(TokenType::getUndefined(), TokenType::fromToken("false"));
    }

    public function testSimpleExpressionEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("true")->stringValue(), "true");
        $this->assertSame($parser->evaluateExpression("\"red\"")->stringValue(), "red");
        $this->assertSame($parser->evaluateExpression("and(true, or(true, true))")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("and(true, or(false, true))")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("not(and(false, or(false, true)))")->boolValue(), true);
    }

    public function testNumeqExpressionsEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("numeq(\"la la\", \"la la\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numeq(\"la la\", \"la,la\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numeq(\"lala\", \"lala\")")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("numeq(\"10\", \"10\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("numeq(\"10\", 10)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("numeq(10, \"10\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("numeq(10, 10)")->boolValue(), true);

        $this->assertSame($parser->evaluateExpression("numeq(\"10\", \"11\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numeq(\"10\", 11)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numeq(10, \"11\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numeq(10, 11)")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("numne(\"la la\", \"la la\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numne(\"la la\", \"la,la\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numne(\"lala\", \"lala\")")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("numne(\"10\", \"10\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numne(\"10\", 10)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numne(10, \"10\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("numne(10, 10)")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("numne(\"10\", \"11\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("numne(\"10\", 11)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("numne(10, \"11\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("numne(10, 11)")->boolValue(), true);
    }

    public function testEqExpressionsEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("eq(\"la la\", \"la la\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("eq(\"la la\", \"la,la\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("eq(\"lala\", \"lala\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("eq(\"10\", \"10\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("eq(\"10\", 10)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("eq(10, 10)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("ne(100.123, 100.321)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("not(eq(undefined, undefined))")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("not(eq(not(undefined), undefined))")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("not(undefined)")->boolValue(), true);

        $roxxString = "la \\\"la\\\" la";
        $this->assertSame(
            $parser->evaluateExpression(sprintf("eq(\"%s\", \"la \\\"la\\\" la\")", $roxxString))->boolValue(), true);
    }

    public function testComparisonExpressionsEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("lt(500, 100)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("lt(500, 500)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("lt(500, 500.54)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("lt(500, \"500.54\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("lt(500, \"500.54a\")")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("lte(500, 500)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("lte(\"500\", 501)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("lte(\"501\", \"500\")")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("gt(500, 100)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gt(500, 500)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("gt(500, \"500\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("gt(500.54, 500)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gt(\"500.54\", 500)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gt(\"50a\", 500)")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("gte(500, 500)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gte(\"500\", 500)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gte(\"505a\", 500)")->boolValue(), false);
    }

    public function testSemVerComparisonEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("semverLt(\"1.1.0\", \"1.1\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverLte(\"1.1.0\", \"1.1\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverGte(\"1.1.0\", \"1.1\")")->boolValue(), true);

        $this->assertSame($parser->evaluateExpression("semverEq(\"1.0.0\", \"1\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverNe(\"1.0.1\", \"1.0.0.1\")")->boolValue(), true);

        $this->assertSame($parser->evaluateExpression("semverLt(\"1.1\", \"1.2\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("semverLte(\"1.1\", \"1.2\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("semverGt(\"1.1.1\", \"1.2\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverGt(\"1.2.1\", \"1.2\")")->boolValue(), true);
    }

    public function testComparisonWithUndefinedEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("gte(500, undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("gt(500, undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("lte(500, undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("lt(500, undefined)")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("semverGte(\"1.1\", undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverGt(\"1.1\", undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverLte(\"1.1\", undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("semverLt(\"1.1\", undefined)")->boolValue(), false);
    }

    public function testUnknownOperatorEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("NOT_AN_OPERATOR(500, 500)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("JUSTAWORD(500, 500)")->boolValue(), false);
    }

    public function testUndefinedEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("isUndefined(undefined)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("isUndefined(123123)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("isUndefined(\"undefined\")")->boolValue(), false);
    }

    public function testNowEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("gte(now(), now())")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gte(now(), 2458.123)")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("gte(now(), 1534759307565)")->boolValue(), true);
    }

    public function testRegularExpressionEvaluation()
    {
        $parser = new Parser();

        $this->assertSame($parser->evaluateExpression("match(\"111\", \"222\", \"\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("match(\".*\", \"222\", \"\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("match(\"22222\", \".*\", \"\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"22222\", \"^2*$\", \"\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"test@shimi.com\", \".*(com|ca)\", \"\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"test@jet.com\", \".*jet\\.com$\", \"\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"US\", \".*IL|US\", \"\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"US\", \"IL|US\"), \"\"")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"US\", \"(IL|US)\", \"\")")->boolValue(), true);

        // Test flags
        $this->assertSame($parser->evaluateExpression("match(\"Us\", \"(IL|US)\", \"\")")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("match(\"uS\", \"(IL|US)\", \"i\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"uS\", \"IL|US#Comment\", \"xi\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"\n\", \".\", \"s\")")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("match(\"HELLO\nTeST\n#This is a comment\", \"^TEST$\", \"ixm\")")->boolValue(), true);
    }

    public function testIfThenExpressionEvaluationString()
    {
        $parser = new Parser();

        $this->assertSame("AB", $parser->evaluateExpression("ifThen(and(true, or(true, true)), \"AB\", \"CD\")")->stringValue());
        $this->assertSame("CD", $parser->evaluateExpression("ifThen(and(false, or(true, true)), \"AB\", \"CD\")")->stringValue());

        $this->assertSame("AB", $parser->evaluateExpression("ifThen(and(true, or(true, true)), \"AB\", ifThen(and(true, or(true, true)), \"EF\", \"CD\"))")->stringValue());
        $this->assertSame("EF", $parser->evaluateExpression("ifThen(and(false, or(true, true)), \"AB\", ifThen(and(true, or(true, true)), \"EF\", \"CD\"))")->stringValue());
        $this->assertSame("CD", $parser->evaluateExpression("ifThen(and(false, or(true, true)), \"AB\", ifThen(and(true, or(false, false)), \"EF\", \"CD\"))")->stringValue());

        $this->assertSame(null, $parser->evaluateExpression("ifThen(and(false, or(true, true)), \"AB\", ifThen(and(true, or(false, false)), \"EF\", undefined))")->stringValue());
    }

    public function testIfThenExpressionEvaluationIntNumber()
    {
        $parser = new Parser();

        $this->assertSame(1, $parser->evaluateExpression("ifThen(and(true, or(true, true)), 1, 2)")->integerValue());
        $this->assertSame(2, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1, 2)")->integerValue());

        $this->assertSame(1, $parser->evaluateExpression("ifThen(and(true, or(true, true)), 1, ifThen(and(true, or(true, true)), 3, 2))")->integerValue());
        $this->assertSame(3, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1, ifThen(and(true, or(true, true)), 3, 2))")->integerValue());
        $this->assertSame(2, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1, ifThen(and(true, or(false, false)), 3, 2))")->integerValue());

        $this->assertSame(null, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1, ifThen(and(true, or(false, false)), 3, undefined))")->integerValue());
    }

    public function testIfThenExpressionEvaluationDoubleNumber()
    {
        $parser = new Parser();

        $this->assertSame(1.1, $parser->evaluateExpression("ifThen(and(true, or(true, true)), 1.1, 2.2)")->doubleValue());
        $this->assertSame(2.2, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1.1, 2.2)")->doubleValue());

        $this->assertSame(1.1, $parser->evaluateExpression("ifThen(and(true, or(true, true)), 1.1, ifThen(and(true, or(true, true)), 3.3, 2.2))")->doubleValue());
        $this->assertSame(3.3, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1.1, ifThen(and(true, or(true, true)), 3.3, 2.2))")->doubleValue());
        $this->assertSame(2.2, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1.1, ifThen(and(true, or(false, false)), 3.3, 2.2))")->doubleValue());

        $this->assertSame(null, $parser->evaluateExpression("ifThen(and(false, or(true, true)), 1.1, ifThen(and(true, or(false, false)), 3.3, undefined))")->doubleValue());
    }

    public function testIfThenExpressionEvaluationBoolean()
    {
        $parser = new Parser();

        $this->assertSame(true, $parser->evaluateExpression("ifThen(and(true, or(true, true)), true, false)")->boolValue());
        $this->assertSame(false, $parser->evaluateExpression("ifThen(and(false, or(true, true)), true, false)")->boolValue());

        $this->assertSame(false, $parser->evaluateExpression("ifThen(and(true, or(true, true)), false, ifThen(and(true, or(true, true)), true, true))")->boolValue());
        $this->assertSame(true, $parser->evaluateExpression("ifThen(and(false, or(true, true)), false, ifThen(and(true, or(true, true)), true, false))")->boolValue());
        $this->assertSame(false, $parser->evaluateExpression("ifThen(and(false, or(true, true)), true, ifThen(and(true, or(false, false)), true, false))")->boolValue());

        $this->assertSame(true, $parser->evaluateExpression("ifThen(and(false, or(true, true)), false, ifThen(and(true, or(false, false)), false, (and(true,true))))")->boolValue());
        $this->assertSame(false, $parser->evaluateExpression("ifThen(and(false, or(true, true)), true, ifThen(and(true, or(false, false)), true, (and(true,false))))")->boolValue());

        $this->assertSame(null, $parser->evaluateExpression("ifThen(and(false, or(true, true)), true, ifThen(and(true, or(false, false)), true, undefined))")->boolValue());
    }

    public function testInArray()
    {
        $parser = new Parser();
        $experimentsExtensions = new ExperimentsExtensions($parser, new TargetGroupRepository(), new FlagRepository(), new ExperimentRepository());
        $roxxPropertiesExtensions = new PropertiesExtensions($parser, new CustomPropertyRepository(), new DynamicProperties());
        $experimentsExtensions->extend();
        $roxxPropertiesExtensions->extend();

        $this->assertSame($parser->evaluateExpression("inArray(\"123\", [\"222\", \"233\"])")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("inArray(\"123\", [\"123\", \"233\"])")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("inArray(\"123\", [123, \"233\"])")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("inArray(\"123\", [123, \"123\", \"233\"])")->boolValue(), true);

        $this->assertSame($parser->evaluateExpression("inArray(123, [123, \"233\"])")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("inArray(123, [\"123\", \"233\"])")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("inArray(\"123\", [])")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("inArray(\"1 [23\", [\"1 [23\", \"]\"])")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("inArray(\"123\", undefined)")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("inArray(undefined, [])")->boolValue(), false);
        $this->assertSame($parser->evaluateExpression("inArray(undefined, [undefined, 123])")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("inArray(undefined, undefined)")->boolValue(), false);

        $this->assertSame($parser->evaluateExpression("inArray(mergeSeed(\"123\", \"456\"), [\"123.456\", \"233\"])")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("inArray(\"123.456\", [mergeSeed(\"123\", \"456\"), \"233\"])")->boolValue(), false); // THIS CASE IS NOT SUPPORTED

        $this->assertSame($parser->evaluateExpression("md5(\"stam\")")->stringValue(), "07915255d64730d06d2349d11ac3bfd8");
        $this->assertSame($parser->evaluateExpression("concat(\"stam\",\"stam2\")")->stringValue(), "stamstam2");
        $this->assertSame($parser->evaluateExpression("inArray(md5(concat(\"st\",\"am\")), [\"07915255d64730d06d2349d11ac3bfd8\"]")->boolValue(), true);
        $this->assertSame($parser->evaluateExpression("eq(md5(concat(\"st\",property(\"notProp\"))), undefined)")->boolValue(), true);

        $this->assertSame($parser->evaluateExpression("b64d(\"c3RhbQ==\")")->stringValue(), "stam");
        $this->assertSame($parser->evaluateExpression("b64d(\"8Km4vQ==\")")->stringValue(), "𩸽");
    }
}
