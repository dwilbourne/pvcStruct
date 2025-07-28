<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\dto\TreenodeDto;

class TreenodeDtoTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDto::__construct
     * @covers \pvc\struct\tree\dto\TreenodeDto::getNodeId
     * @covers \pvc\struct\tree\dto\TreenodeDto::getParentId
     * @covers \pvc\struct\tree\dto\TreenodeDto::getTreeId
     * @covers \pvc\struct\tree\dto\TreenodeDto::getIndex
     */
    public function testConstructAndGetters()
    {
        $nodeId = 1;
        $parentId = 2;
        $treeId = 3;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);
        self::assertInstanceOf(TreenodeDto::class, $dto);

        self::assertEquals($nodeId, $dto->getNodeId());
        self::assertEquals($parentId, $dto->getParentId());
        self::assertEquals($treeId, $dto->getTreeId());
        self::assertNull($dto->getIndex());
    }
}
