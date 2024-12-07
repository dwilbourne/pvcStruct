<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\struct\tree\dto\TreenodeDTOUnordered;

class TreenodeDTOUnorderedTest extends TestCase
{
    protected TreenodeDTOUnordered $dto;

    protected int $nodeId = 1;

    protected int $parentId = 4;

    protected int $treeId = 7;

    protected string $payload = 'foo';

    public function setUp(): void
    {
        $this->dto = new TreenodeDTOUnordered();
    }

    protected function assertPropertiesAreSet(): void
    {
        self::assertEquals($this->nodeId, $this->dto->nodeId);
        self::assertEquals($this->parentId, $this->dto->parentId);
        self::assertEquals($this->treeId, $this->dto->treeId);
        self::assertEquals($this->payload, $this->dto->payload);
    }

    /**
     * testHydrateFromNode
     * @covers \pvc\struct\tree\dto\TreenodeDTOUnordered::hydrateFromNode
     */
    public function testHydrateFromNode(): void
    {
        $node = $this->createMock(TreenodeUnorderedInterface::class);
        $node->method('getNodeId')->willReturn($this->nodeId);
        $node->method('getParentId')->willReturn($this->parentId);
        $node->method('getTreeId')->willReturn($this->treeId);
        $node->method('getPayload')->willReturn($this->payload);

        $this->dto->hydrateFromNode($node);
        $this->assertPropertiesAreSet();
    }

    /**
     * testHydrateFromArray
     * @covers \pvc\struct\tree\dto\TreenodeDTOUnordered::hydrateFromArray
     */
    public function testHydrateFromArray(): void
    {
        $array = [];
        $array['nodeId'] = $this->nodeId;
        $array['parentId'] = $this->parentId;
        $array['treeId'] = $this->treeId;
        $array['payload'] = $this->payload;

        $this->dto->hydrateFromArray($array);
        $this->assertPropertiesAreSet();
    }
}
