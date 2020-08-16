<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element;


use Mockery;
use pvc\formatter\numeric\FrmtrFloat;
use pvc\struct\range_collection\range_element\RangeElementFloat;

/**
 * Class RangeElementFloatTest
 * @package tests\validator\base\min_max\elements
 */
class RangeElementFloatTest extends RangeElementTestCase
{
    public function setUp() : void
    {
        $this->element = new RangeElementFloat();

        $this->frmtr = Mockery::mock(FrmtrFloat::class);
        $this->frmtr->shouldReceive('format')->andReturnArg(0);

        $this->defaultMin = -PHP_FLOAT_MAX;
        $this->testMin = -100.1;

        $this->defaultMax = PHP_FLOAT_MAX;
        $this->testMax = 100.2;

        $this->valueLessThanMin = -200.3;
        $this->valueBetweenMinAndMax = 0.1;
        $this->valueGreaterThanMax = 200.4;
    }
}
