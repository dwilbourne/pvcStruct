<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\dto\TreenodeDto;

class TreenodeDtoUnorderedTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDto::__construct
     */
    public function testConstruct()
    {
        $nodeId = 1;
        $parentId = 2;
        $treeId = 3;
        $dto = new TreenodeDto($nodeId, $parentId, $treeId);
        self::assertInstanceOf(TreenodeDto::class, $dto);
    }
}
