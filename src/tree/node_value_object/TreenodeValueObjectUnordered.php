<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectUnorderedInterface;

/**
 * Class TreenodeValueObjectUnordered
 * @template PayloadType of HasPayloadInterface
 * @extends TreenodeValueObjectAbstract<PayloadType, TreenodeValueObjectUnorderedInterface>
 * @implements TreenodeValueObjectUnorderedInterface<PayloadType>
 */
class TreenodeValueObjectUnordered extends TreenodeValueObjectAbstract implements TreenodeValueObjectUnorderedInterface
{
    /**
     * hydrateFromNode
     * @param TreenodeUnorderedInterface<PayloadType> $node
     */
    public function hydrateFromNode(TreenodeUnorderedInterface $node): void
    {
        $this->setNodeId($node->getNodeId());
        $this->setParentId($node->getParentId());
        $this->setTreeId($node->getTreeId());
        $this->setPayload($node->getPayload());
    }

    /**
     * hydrateFromArray
     * @param array{
     *     'nodeId': non-negative-int,
     *     'parentId': non-negative-int|null,
     *     'treeId': non-negative-int,
     *     'payload': PayloadType,
     * } $nodeData
     */
    public function hydrateFromAssociativeArray(array $nodeData): void
    {
        $this->setNodeId($nodeData['nodeId']);
        $this->setParentId($nodeData['parentId']);
        $this->setTreeId($nodeData['treeId']);
        $this->setPayload($nodeData['payload']);
    }

    /**
     * hydrateFromNumericArray
     * @param array{
     *        0: non-negative-int,
     *        1: non-negative-int|null,
     *        2: non-negative-int,
     *        3: PayloadType,
     *    } $nodeData
     */
    public function hydrateFromNumericArray(array $nodeData): void
    {
        $this->setNodeId($nodeData[0]);
        $this->setParentId($nodeData[1]);
        $this->setTreeId($nodeData[2]);
        $this->setPayload($nodeData[3]);
    }
}
