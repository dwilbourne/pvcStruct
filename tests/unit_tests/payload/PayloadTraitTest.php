<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValidatorInterface;
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
     * testSetGetValueValidator
     * @covers pvc\struct\payload\PayloadTrait::SetValueValidator
     * @covers pvc\struct\payload\PayloadTrait::GetValueValidator
     */
    public function testSetGetValueValidator(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $this->mockTrait->SetValueValidator($validator);
        self::assertEquals($validator, $this->mockTrait->GetValueValidator());
    }

    /**
     * testSetValueThrowsExceptionWhenValidatorFails
     * @throws InvalidValueException
     * @covers pvc\struct\payload\PayloadTrait::setValue
     */
    public function testSetValueThrowsExceptionWhenValidatorFails(): void
    {
        $validator = $this->createStub(ValidatorInterface::class);
        $validator->method('validate')->willReturn(false);
        $this->mockTrait->SetValueValidator($validator);

        $testValue = 'foo';
        self::expectException(InvalidValueException::class);
        $this->mockTrait->setValue($testValue);
    }

    /**
     * testSetGetValue
     * @throws InvalidValueException
     * @covers pvc\struct\payload\PayloadTrait::setValue
     * @covers pvc\struct\payload\PayloadTrait::getValue
     */
    public function testSetGetValue(): void
    {
        $validator = $this->createStub(ValidatorInterface::class);
        $validator->method('validate')->willReturn(true);
        $this->mockTrait->SetValueValidator($validator);

        $testValue = 'foo';
        $this->mockTrait->setValue($testValue);
        self::assertEquals($testValue, $this->mockTrait->getValue());
    }
}
