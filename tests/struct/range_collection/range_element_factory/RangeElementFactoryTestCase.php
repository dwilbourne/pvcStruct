<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection\range_element_factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\range_collection\range_element\RangeElementInterface;

/**
 * Class RangeElementFactoryTestCase
 * @package tests\struct\range_collection\range_element_factory
 */
class RangeElementFactoryTestCase extends TestCase
{
    protected $rangeElementFactory;
    protected $min;
    protected $max;

    public function testCreation() : void
    {
        self::assertInstanceOf(
            RangeElementInterface::class,
            $this->rangeElementFactory->createRangeElement($this->min, $this->max)
        );
    }
}
