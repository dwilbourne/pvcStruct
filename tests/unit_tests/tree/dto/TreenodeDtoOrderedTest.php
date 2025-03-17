<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\dto\TreenodeDtoOrdered;

class TreenodeDtoOrderedTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoOrdered::__construct
     */
    public function testConstruct()
    {
        $nodeId = 1;
        $parentId = 2;
        $treeId = 3;
        $payLoad = 4;
        $index = 5;
        $dto = new TreenodeDtoOrdered($nodeId, $parentId, $treeId, $payLoad, $index);
        self::assertInstanceOf(TreenodeDtoOrdered::class, $dto);
    }
}
