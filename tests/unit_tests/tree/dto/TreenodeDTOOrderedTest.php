<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\struct\tree\dto\TreenodeDTOOrdered;

class TreenodeDTOOrderedTest extends TestCase
{
    protected TreenodeDTOOrdered $dto;

    protected int $nodeId = 1;

    protected int $parentId = 4;

    protected int $treeId = 7;

    protected string $payload = 'foo';

    protected int $index = 0;

    public function setUp(): void
    {
        $this->dto = new TreenodeDTOOrdered();
    }

    protected function assertPropertiesAreSet(): void
    {
        self::assertEquals($this->nodeId, $this->dto->nodeId);
        self::assertEquals($this->parentId, $this->dto->parentId);
        self::assertEquals($this->treeId, $this->dto->treeId);
        self::assertEquals($this->payload, $this->dto->payload);
        self::assertEquals($this->index, $this->dto->index);
    }

    /**
     * testHydrateFromNode
     * @covers \pvc\struct\tree\dto\TreenodeDTOOrdered::hydrateFromNode
     */
    public function testHydrateFromNode(): void
    {
        $node = $this->createMock(TreenodeOrderedInterface::class);
        $node->method('getNodeId')->willReturn($this->nodeId);
        $node->method('getParentId')->willReturn($this->parentId);
        $node->method('getTreeId')->willReturn($this->treeId);
        $node->method('getPayload')->willReturn($this->payload);
        $node->method('getIndex')->willReturn($this->index);

        $this->dto->hydrateFromNode($node);
        $this->assertPropertiesAreSet();
    }

    /**
     * testHydrate
     * @covers \pvc\struct\tree\dto\TreenodeDTOOrdered::hydrateFromArray
     */
    public function testHydrateFromArray(): void
    {
        $array = [];
        $array['nodeId'] = $this->nodeId;
        $array['parentId'] = $this->parentId;
        $array['treeId'] = $this->treeId;
        $array['payload'] = $this->payload;
        $array['index'] = $this->index;

        $this->dto->hydrateFromArray($array);
        $this->assertPropertiesAreSet();
    }
}
