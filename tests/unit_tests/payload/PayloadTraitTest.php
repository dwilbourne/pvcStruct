<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValTesterInterface;
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
     * @return void
     * @covers \pvc\struct\payload\PayloadTrait::setPayloadTester
     * @covers \pvc\struct\payload\PayloadTrait::getPayloadTester
     */
    public function testSetGetPayloadTester() : void
    {
        /**
         * test for default value tester, which should always return true
         */
        self::assertInstanceOf(ValTesterInterface::class, $this->mockTrait->getPayloadTester());

        /**
         * test that you can set payload with the default payloadtester
         */
        $anyValue  = 'foo';
        $this->mockTrait->setPayload($anyValue);

        $tester = $this->createMock(ValTesterInterface::class);
        $this->mockTrait->setPayloadTester($tester);
        self::assertSame($tester, $this->mockTrait->getPayloadTester());
    }

    /**
     * testSetPayloadThrowsExceptionWhenValidatorFails
     * @throws InvalidValueException
     * @covers \pvc\struct\payload\PayloadTrait::setPayload
     */
    public function testSetPayloadThrowsExceptionWhenValidatorFails(): void
    {
        $validator = $this->createStub(ValTesterInterface::class);
        $validator->method('testValue')->willReturn(false);
        $this->mockTrait->setPayloadTester($validator);

        $testValue = 'foo';
        self::expectException(InvalidValueException::class);
        $this->mockTrait->setPayload($testValue);
    }

    /**
     * testSetGetValue
     * @throws InvalidValueException
     * @covers \pvc\struct\payload\PayloadTrait::setPayload
     * @covers \pvc\struct\payload\PayloadTrait::getPayload
     */
    public function testSetGetPayload(): void
    {
        $tester = $this->createStub(ValTesterInterface::class);
        $tester->method('testValue')->willReturn(true);
        $this->mockTrait->setPayloadTester($tester);

        $testValue = 'foo';
        $this->mockTrait->setPayload($testValue);
        self::assertEquals($testValue, $this->mockTrait->getPayload());
    }
}
