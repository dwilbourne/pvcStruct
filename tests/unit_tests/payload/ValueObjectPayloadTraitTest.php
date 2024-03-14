<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\payload\ValueObjectPayloadTrait;

class ValueObjectPayloadTraitTest extends TestCase
{
    /**
     * @var ValueObjectPayloadTrait|MockObject $mockTrait
     */
    protected $mockTrait;

    public function setUp(): void
    {
        $this->mockTrait = $this->getMockForTrait(ValueObjectPayloadTrait::class);
    }

    /**
     * testSetGetPayload
     * @covers pvc\struct\payload\ValueObjectPayloadTrait::setPayload
     * @covers pvc\struct\payload\ValueObjectPayloadTrait::getPayload
     */
    public function testSetGetPayload(): void
    {
        $payload = 'foo';
        $this->mockTrait->setPayload($payload);
        self::assertEquals($payload, $this->mockTrait->getPayload());
    }
}
