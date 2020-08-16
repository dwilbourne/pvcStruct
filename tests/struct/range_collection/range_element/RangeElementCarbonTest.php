<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element;

use Carbon\Carbon;
use Mockery;
use pvc\formatter\date_time\FrmtrDateTimeAbstract;
use pvc\struct\range_collection\range_element\RangeElementCarbon;

class RangeElementCarbonTest extends RangeElementTestCase
{

    public function setUp() : void
    {
        $this->element = new RangeElementCarbon();

        $this->frmtr = Mockery::mock(FrmtrDateTimeAbstract::class);
        $this->frmtr->shouldReceive('format')->andReturnArg(0);


        $this->defaultMin = new Carbon(RangeElementCarbon::MIN_CARBON_STRING);
        $this->testMin = new Carbon('2000-01-01');

        $this->defaultMax = new Carbon(RangeElementCarbon::MAX_CARBON_STRING);
        $this->testMax = new Carbon('2020-12-31');

        $this->valueLessThanMin = new Carbon('1970-01-01');
        $this->valueBetweenMinAndMax = new Carbon('2015-01-01');
        $this->valueGreaterThanMax = new Carbon('2025-01-01');
    }

}
