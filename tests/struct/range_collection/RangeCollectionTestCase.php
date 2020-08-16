<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\struct\range_collection;

use PHPUnit\Framework\TestCase;

class RangeCollectionTestCase extends TestCase
{
    /** @phpstan-ignore-next-line  */
    protected $rangeCollection;

    /** @phpstan-ignore-next-line  */
    protected $rangeElementA;

    /** @phpstan-ignore-next-line  */
    protected $rangeElementB;

    public function testAddRangeElement() : void
    {
        $this->rangeCollection->addRangeElement($this->rangeElementA);
        self::assertEqualsCanonicalizing([$this->rangeElementA], $this->rangeCollection->getRangeElements());

        $this->rangeCollection->addRangeElement($this->rangeElementB);
        self::assertEqualsCanonicalizing([$this->rangeElementA, $this->rangeElementB], $this->rangeCollection->getRangeElements());
    }
}
