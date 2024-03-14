<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\payload;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\struct\payload\PayloadValidatorTrait;

class PayloadValidatorTraitTest extends TestCase
{
    /**
     * @var PayloadValidatorTrait|MockObject
     */
    protected $mock;

    public function setUp(): void
    {
        $this->mock = $this->getMockForTrait(PayloadValidatorTrait::class);
    }

    /**
     * testSetGetPayloadValidator
     * @covers pvc\struct\payload\PayloadValidatorTrait::getPayloadValidator
     * @covers pvc\struct\payload\PayloadValidatorTrait::setPayloadValidator
     */
    public function testSetGetPayloadValidator(): void
    {
        $mockValidator = $this->createMock(ValidatorPayloadInterface::class);
        $this->mock->setPayloadValidator($mockValidator);
        self::assertEquals($mockValidator, $this->mock->getPayloadValidator());
    }
}
