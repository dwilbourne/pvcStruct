<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\struct\payload\PayloadTrait;
use pvc\struct\tree\err\InvalidValueException;

class PayloadTraitTest extends TestCase
{
    protected $mockTrait;

    public function setUp(): void
    {
        $this->mockTrait = $this->getMockForTrait(PayloadTrait::class);
    }

    /**
     * testSetPayloadThrowsExceptionWhenValidatorFails
     * @throws InvalidValueException
     * @covers pvc\struct\payload\PayloadTrait::setPayload
     */
    public function testSetPayloadThrowsExceptionWhenValidatorFails(): void
    {
        $validator = $this->createStub(PayloadTesterInterface::class);
        $validator->method('testValue')->willReturn(false);
        $this->mockTrait->setPayloadTester($validator);

        $testValue = 'foo';
        self::expectException(InvalidValueException::class);
        $this->mockTrait->setPayload($testValue);
    }

    /**
     * testSetGetValue
     * @throws InvalidValueException
     * @covers pvc\struct\payload\PayloadTrait::setPayload
     * @covers pvc\struct\payload\PayloadTrait::getPayload
     */
    public function testSetGetPayload(): void
    {
        $tester = $this->createStub(PayloadTesterInterface::class);
        $tester->method('testValue')->willReturn(true);
        $this->mockTrait->setPayloadTester($tester);

        $testValue = 'foo';
        $this->mockTrait->setPayload($testValue);
        self::assertEquals($testValue, $this->mockTrait->getPayload());
    }
}
