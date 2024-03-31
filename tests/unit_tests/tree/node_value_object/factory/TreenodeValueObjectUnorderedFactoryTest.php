<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node_value_object\factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\node_value_object\factory\TreenodeValueObjectUnorderedFactory;
use pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered;

class TreenodeValueObjectUnorderedFactoryTest extends TestCase
{
    protected TreenodeValueObjectUnorderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new TreenodeValueObjectUnorderedFactory();
    }

    /**
     * testMakeValueObject
     * @covers \pvc\struct\tree\node_value_object\factory\TreenodeValueObjectUnorderedFactory::makeValueObject
     */
    public function testMakeValueObject(): void
    {
        self::assertInstanceOf(TreenodeValueObjectUnordered::class, $this->factory->makeValueObject());
    }
}
