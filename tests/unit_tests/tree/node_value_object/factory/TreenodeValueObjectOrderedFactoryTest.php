<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node_value_object\factory;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactory;
use pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered;

class TreenodeValueObjectOrderedFactoryTest extends TestCase
{
    protected TreenodeValueObjectOrderedFactory $factory;

    public function setUp(): void
    {
        $this->factory = new TreenodeValueObjectOrderedFactory();
    }

    /**
     * testMakeValueObject
     * @covers \pvc\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactory::makeValueObject
     */
    public function testMakeValueObject(): void
    {
        self::assertInstanceOf(TreenodeValueObjectOrdered::class, $this->factory->makeValueObject());
    }
}
