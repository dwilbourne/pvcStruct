<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection\factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\CollectionUnordered;
use pvc\struct\collection\factory\CollectionUnorderedFactory;

class CollectionUnorderedFactoryTest extends TestCase
{
    protected CollectionUnorderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new CollectionUnorderedFactory();
    }

    /**
     * testMakeCollection
     * @covers \pvc\struct\collection\factory\CollectionUnorderedFactory::makeCollection
     */
    public function testMakeCollection(): void
    {
        self::assertInstanceOf(CollectionUnordered::class, $this->factory->makeCollection());
    }
}

