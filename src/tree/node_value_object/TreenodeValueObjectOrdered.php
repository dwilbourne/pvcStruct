<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectOrderedInterface;

/**
 * Class TreenodeValueObjectOrdered
 * @template PayloadType of HasPayloadInterface
 * @extends TreenodeValueObjectAbstract<PayloadType, TreenodeValueObjectOrderedInterface>
 * @implements TreenodeValueObjectOrderedInterface<PayloadType>
 */
class TreenodeValueObjectOrdered extends TreenodeValueObjectAbstract implements TreenodeValueObjectOrderedInterface
{
    /**
     * @var non-negative-int
     */
    protected int $index;

    /**
     * hydrateFromNode
     * @param TreenodeOrderedInterface<PayloadType> $node
     */
    public function hydrateFromNode(TreenodeOrderedInterface $node): void
    {
        $this->setNodeId($node->getNodeId());
        $this->setParentId($node->getParentId());
        $this->setTreeId($node->getTreeId());
        $this->setPayload($node->getPayload());
        $this->setIndex($node->getIndex());
    }

    /**
     * getIndex
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * setIndex
     * @param non-negative-int $index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * hydrateFromArray
     * @param array{
     *     'nodeId': non-negative-int,
     *     'parentId': non-negative-int|null,
     *     'treeId': non-negative-int,
     *     'payload': PayloadType,
     *     'index': non-negative-int
     * } $nodeData
     */
    public function hydrateFromAssociativeArray(array $nodeData): void
    {
        $this->setNodeId($nodeData['nodeId']);
        $this->setParentId($nodeData['parentId']);
        $this->setTreeId($nodeData['treeId']);
        $this->setPayload($nodeData['payload']);
        $this->setIndex($nodeData['index']);
    }

    /**
     * hydrateFromNumericArray
     * @param array{
     *        0: non-negative-int,
     *        1: non-negative-int|null,
     *        2: non-negative-int,
     *        3: PayloadType,
     *        4: non-negative-int,
     *    } $nodeData
     */
    public function hydrateFromNumericArray(array $nodeData): void
    {
        $this->setNodeId($nodeData[0]);
        $this->setParentId($nodeData[1]);
        $this->setTreeId($nodeData[2]);
        $this->setPayload($nodeData[3]);
        $this->setIndex($nodeData[4]);
    }
}
