<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\dto\factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\dto\factory\TreenodeDTOOrderedFactory;
use pvc\struct\tree\dto\TreenodeDTOOrdered;

class TreenodeDTOOrderedFactoryTest extends TestCase
{
    protected TreenodeDTOOrderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new TreenodeDTOOrderedFactory();
    }

    /**
     * testMakeDTO
     * @covers \pvc\struct\tree\dto\factory\TreenodeDTOOrderedFactory::makeDTO
     */
    public function testMakeDTO(): void
    {
        self::assertInstanceOf(TreenodeDTOOrdered::class, $this->factory->makeDTO());
    }
}
