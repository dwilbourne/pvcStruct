<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\node\TreenodeValueValidatorDefault;

class TreenodeValueValidatorDefaultTest extends TestCase
{
    /**
     * @var TreenodeValueValidatorDefault
     */
    protected TreenodeValueValidatorDefault $validator;

    public function setUp(): void
    {
        $this->validator = new TreenodeValueValidatorDefault();
    }

    /**
     * testGetMsg
     * @covers \pvc\struct\tree\node\TreenodeValueValidatorDefault::getMsg
     */
    public function testGetMsg(): void
    {
        self::assertEquals('', $this->validator->getMsg());
    }

    /**
     * testValidateReturnsTrue
     * @covers \pvc\struct\tree\node\TreenodeValueValidatorDefault::validate
     */
    public function testValidateReturnsTrue(): void
    {
        self::assertTrue($this->validator->validate(new \stdClass()));
    }
}
