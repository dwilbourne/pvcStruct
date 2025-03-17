<?php

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\dto\TreenodeDtoUnordered;

class TreenodeDtoUnorderedTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoUnordered::__construct
     */
    public function testConstruct()
    {
        $nodeId = 1;
        $parentId = 2;
        $treeId = 3;
        $payLoad = 4;
        $dto = new TreenodeDtoUnordered($nodeId, $parentId, $treeId, $payLoad);
        self::assertInstanceOf(TreenodeDtoUnordered::class, $dto);
    }
}
