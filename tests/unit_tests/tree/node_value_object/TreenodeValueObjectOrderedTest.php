<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node_value_object;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered;

class TreenodeValueObjectOrderedTest extends TestCase
{
    protected TreenodeValueObjectOrdered $valueObject;

    protected int $nodeId = 1;

    protected int $parentId = 4;

    protected int $treeId = 7;

    protected string $value = 'foo';

    protected int $index = 0;

    public function setUp(): void
    {
        $this->valueObject = new TreenodeValueObjectOrdered();
    }

    /**
     * testHydrateFromNode
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::hydrateFromNode
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::getIndex
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::setIndex
     */
    public function testHydrateFromNode(): void
    {
        $node = $this->createMock(TreenodeOrderedInterface::class);
        $node->method('getNodeId')->willReturn($this->nodeId);
        $node->method('getParentId')->willReturn($this->parentId);
        $node->method('getTreeId')->willReturn($this->treeId);
        $node->method('getValue')->willReturn($this->value);
        $node->method('getIndex')->willReturn($this->index);

        $this->valueObject->hydrateFromNode($node);

        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
        self::assertEquals($this->value, $this->valueObject->getValue());
        self::assertEquals($this->index, $this->valueObject->getIndex());
    }

    /**
     * testHydrateFromAssociativeArray
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::hydrateFromAssociativeArray
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::setIndex
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
        $array['value'] = $this->value;
        $array['index'] = $this->index;

        $this->valueObject->hydrateFromAssociativeArray($array);

        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
        self::assertEquals($this->value, $this->valueObject->getValue());
        self::assertEquals($this->index, $this->valueObject->getIndex());
    }

    /**
     * testHydrateFromNumericArray
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::hydrateFromNumericArray
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered::setIndex
     */
    public function testHydrateFromNumericArray(): void
    {
        $array = [];
        $array[] = $this->nodeId;
        $array[] = $this->parentId;
        $array[] = $this->treeId;
        $array[] = $this->value;
        $array[] = $this->index;

        $this->valueObject->hydrateFromNumericArray($array);

        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
        self::assertEquals($this->value, $this->valueObject->getValue());
        self::assertEquals($this->index, $this->valueObject->getIndex());
    }
}
