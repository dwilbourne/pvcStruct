<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection\factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\factory\CollectionOrderedFactory;

class CollectionOrderedFactoryTest extends TestCase
{
    protected CollectionOrderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new CollectionOrderedFactory();
    }

    /**
     * testMakeCollection
     * @covers \pvc\struct\collection\factory\CollectionOrderedFactory::makeCollection
     */
    public function testMakeCollection(): void
    {
        self::assertInstanceOf(CollectionOrdered::class, $this->factory->makeCollection());
    }
}
