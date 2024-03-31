<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\struct\payload\PayloadTesterTrait;

class PayloadValidatorTraitTest extends TestCase
{
    /**
     * @var PayloadValidatorTrait|MockObject
     */
    protected $mock;

    public function setUp(): void
    {
        $this->mock = $this->getMockForTrait(PayloadTesterTrait::class);
    }

    /**
     * testSetGetPayloadValidator
     * @covers pvc\struct\payload\PayloadTesterTrait::getPayloadTester
     * @covers pvc\struct\payload\PayloadTesterTrait::setPayloadTester
     */
    public function testSetGetPayloadValidator(): void
    {
        $mockTester = $this->createMock(PayloadTesterInterface::class);
        $this->mock->setPayloadTester($mockTester);
        self::assertEquals($mockTester, $this->mock->getPayloadTester());
    }
}
