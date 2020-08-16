<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element;

use PHPUnit\Framework\TestCase;
use pvc\err\throwable\exception\pvc_exceptions\InvalidValueException;

/**
 * Class MinMaxElementTest
 * @package tests\validator\base\min_max\elements
 */
abstract class RangeElementTestCase extends TestCase
{

    /** @phpstan-ignore-next-line  */
    protected $element;

    /** @phpstan-ignore-next-line  */
    protected $frmtr;

    /** @phpstan-ignore-next-line  */
    protected $defaultMin;

    /** @phpstan-ignore-next-line  */
    protected $testMin;

    /** @phpstan-ignore-next-line  */
    protected $defaultMax;

    /** @phpstan-ignore-next-line  */
    protected $testMax;

    /** @phpstan-ignore-next-line  */
    protected $valueLessThanMin;

    /** @phpstan-ignore-next-line  */
    protected $valueBetweenMinAndMax;

    /** @phpstan-ignore-next-line  */
    protected $valueGreaterThanMax;

    public function testSetGetFrmtr() : void
    {
        $this->element->setFrmtr($this->frmtr);
        self::assertEquals($this->frmtr, $this->element->getFrmtr());
    }

    public function testGetMinDefault() : void
    {
        $expectedResult = $this->defaultMin;
        self::assertEquals($expectedResult, $this->element->getMin());
    }

    public function testGetMaxDefault() : void
    {
        $expectedResult = $this->defaultMax;
        self::assertEquals($expectedResult, $this->element->getMax());
    }

    public function testSetGetMinSucceedsWithNoMaxSet() : void
    {
        $this->element->setMin($this->testMin);
        self::assertEquals($this->testMin, $this->element->getMin());
    }

    public function testSetGetMinSucceedsWithMaxSet() : void
    {
        $this->element->setMax($this->testMax);
        $this->element->setMin($this->testMin);
        self::assertEquals($this->testMin, $this->element->getMin());
    }

    public function testSetGetMinFailsWhenMinGreaterThanMax() : void
    {
        $this->element->setFrmtr($this->frmtr);
        $this->element->setMax($this->testMin);
        self::expectException(InvalidValueException::class);
        $this->element->setMin($this->testMax);
    }

    public function testSetGetMaxSucceedsWithNoMinSet() : void
    {
        $this->element->setMax($this->testMax);
        self::assertEquals($this->testMax, $this->element->getMax());
    }

    public function testSetGetMaxSucceedsWithMinSet() : void
    {
        $this->element->setMin($this->testMin);
        $this->element->setMax($this->testMax);
        self::assertEquals($this->testMax, $this->element->getMax());
    }

    public function testSetGetMaxFailsWhenMaxLessThanMin() : void
    {
        $this->element->setFrmtr($this->frmtr);
        $this->element->setMin($this->testMax);
        self::expectException(InvalidValueException::class);
        $this->element->setMax($this->testMin);
    }

    protected function setupBrackets() : void
    {
        $this->element->setMin($this->testMin);
        $this->element->setMax($this->testMax);
    }

    public function testBracketsFailsWhenValueBelowRange() : void
    {
        $this->setupBrackets();
        self::assertFalse($this->element->brackets($this->valueLessThanMin));
    }

    public function testBracketsSucceedsWhenValueEqualsMin() : void
    {
        $this->setupBrackets();
        self::assertTrue($this->element->brackets($this->testMin));
    }

    public function testBracketsSucceedsWhenValueBetweenMinAndMax() : void
    {
        $this->setupBrackets();
        $this->assertTrue($this->element->brackets($this->valueBetweenMinAndMax));
    }

    public function testBracketsSucceedsWhenValueEqualsMax() : void
    {
        $this->setupBrackets();
        $this->assertTrue($this->element->brackets($this->testMax));
    }

    public function testBracketsFailsWhenValueGreaterThanMax() : void
    {
        $this->setupBrackets();
        $this->assertFalse($this->element->brackets($this->valueGreaterThanMax));
    }
}
