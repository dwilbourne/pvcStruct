<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\dto\factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\dto\factory\TreenodeDTOUnorderedFactory;
use pvc\struct\tree\dto\TreenodeDTOUnordered;

class TreenodeDTOUnorderedFactoryTest extends TestCase
{
    protected TreenodeDTOUnorderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new TreenodeDTOUnorderedFactory();
    }

    /**
     * testMakeDTO
     * @covers \pvc\struct\tree\dto\factory\TreenodeDTOUnorderedFactory::makeDTO
     */
    public function testMakeDTO(): void
    {
        self::assertInstanceOf(TreenodeDTOUnordered::class, $this->factory->makeDTO());
    }
}
