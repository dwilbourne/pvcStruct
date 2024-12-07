<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\struct\payload\DtoPayloadTrait;

class DtoPayloadTraitTest extends TestCase
{
    /**
     * @var DtoPayloadTrait|MockObject $mockTrait
     */
    protected $mockTrait;

    public function setUp(): void
    {
        $this->mockTrait = $this->getMockForTrait(DtoPayloadTrait::class);
    }

    /**
     * testSetGetPayload
     * @covers pvc\struct\payload\DtoPayloadTrait::setPayload
     * @covers pvc\struct\payload\DtoPayloadTrait::getPayload
     */
    public function testSetGetPayload(): void
    {
        $payload = 'foo';
        $this->mockTrait->setPayload($payload);
        self::assertEquals($payload, $this->mockTrait->getPayload());
    }
}
