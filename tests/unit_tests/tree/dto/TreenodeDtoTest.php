<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\struct\tree\dto\TreenodeDto;

/**
 * @template PayloadType
 * @phpstan-import-type TreenodeDtoShape from TreenodeDtoInterface
 */
class TreenodeDtoTest extends TestCase
{
    /**
     * @var TreenodeDto<PayloadType>&TreenodeDtoShape $dto
     */
    protected TreenodeDto $dto;

    protected int $testIndex = 0;

    public function setUp() : void
    {
        $this->dto = new TreenodeDto();
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDto::getIndex
     * @covers \pvc\struct\tree\dto\TreenodeDto::setIndex
     */
    public function testGetIndex(): void
    {
        $this->dto->setIndex($this->testIndex);
        self::assertEquals($this->testIndex, $this->dto->getIndex());
    }
}
