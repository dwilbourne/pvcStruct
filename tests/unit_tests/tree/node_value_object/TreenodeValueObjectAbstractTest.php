<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node_value_object;

use PHPUnit\Framework\TestCase;
use pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract;

class TreenodeValueObjectAbstractTest extends TestCase
{
    protected TreenodeValueObjectAbstract $valueObject;

    protected int $nodeId = 0;

    protected int $parentId = 4;

    protected int $treeId = 7;

    public function setUp(): void
    {
        $this->valueObject = $this->getMockForAbstractClass(TreenodeValueObjectAbstract::class);
        $this->valueObject->setNodeId($this->nodeId);
        $this->valueObject->setParentId($this->parentId);
        $this->valueObject->setTreeId($this->treeId);
    }

    /**
     * testSettersGetters
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::setNodeId
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::getNodeId
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::setParentId
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::getParentId
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::setTreeId
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::getTreeId
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::setPayload
     * @covers \pvc\struct\tree\node_value_object\TreenodeValueObjectAbstract::getPayload
     */
    public function testSettersGetters(): void
    {
        self::assertEquals($this->nodeId, $this->valueObject->getNodeId());
        self::assertEquals($this->parentId, $this->valueObject->getParentId());
        self::assertEquals($this->treeId, $this->valueObject->getTreeId());
    }
}
