<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element;

use Mockery;
use pvc\formatter\numeric\FrmtrInteger;
use pvc\struct\range_collection\range_element\RangeElementInteger;

/**
 * Class RangeElementIntegerTest
 * @package tests\validator\base\min_max\elements
 */
class RangeElementIntegerTest extends RangeElementTestCase
{
    public function setUp() : void
    {
        $this->element = new RangeElementInteger();

        $this->frmtr = Mockery::mock(FrmtrInteger::class);
        $this->frmtr->shouldReceive('format')->andReturnArg(0);

        $this->defaultMin = PHP_INT_MIN;
        $this->testMin = -100;

        $this->defaultMax = PHP_INT_MAX;
        $this->testMax = 100;

        $this->valueLessThanMin = -200;
        $this->valueBetweenMinAndMax = 1;
        $this->valueGreaterThanMax = 200;
    }
}
