<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node_value_object;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered;

class TreenodeValueObjectUnorderedTest extends TestCase
{
    protected TreenodeValueObjectUnordered $valueObject;

    protected int $nodeId = 1;

    protected int $parentId = 4;

    protected int $treeId = 7;

    protected string $payload = 'foo';

    public function setUp(): void
    {
        $this->valueObject = new TreenodeValueObjectUnordered();
    }

    /**
     * testHydrateFromNode
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered::hydrateFromNode
     */
    public function testHydrateFromNode(): void
    {
        $node = $this->createMock(TreenodeUnorderedInterface::class);
        $node->method('getNodeId')->willReturn($this->nodeId);
        $node->method('getParentId')->willReturn($this->parentId);
        $node->method('getTreeId')->willReturn($this->treeId);
        $node->method('getPayload')->willReturn($this->payload);

        $this->valueObject->hydrateFromNode($node);

        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
        self::assertEquals($this->payload, $this->valueObject->getPayload());
    }

    /**
     * testHydrateFromAssociativeArray
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered::hydrateFromAssociativeArray
     *
     * there are no checks on the shape of the incoming array in the code - it is type hinted in the PHPDoc
     *
     */
    public function testHydrateFromAssociativeArray(): void
    {
        $array = [];
        $array['nodeId'] = $this->nodeId;
        $array['parentId'] = $this->parentId;
        $array['treeId'] = $this->treeId;
        $array['payload'] = $this->payload;

        $this->valueObject->hydrateFromAssociativeArray($array);

        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
        self::assertEquals($this->payload, $this->valueObject->getPayload());
    }

    /**
     * testHydrateFromNumericArray
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered::hydrateFromNumericArray
     */
    public function testHydrateFromNumericArray(): void
    {
        $array = [];
        $array[] = $this->nodeId;
        $array[] = $this->parentId;
        $array[] = $this->treeId;
        $array[] = $this->payload;

        $this->valueObject->hydrateFromNumericArray($array);

        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
        self::assertEquals($this->payload, $this->valueObject->getPayload());
    }
}
