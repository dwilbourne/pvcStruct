<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectUnorderedInterface;

/**
 * Class TreenodeValueObjectUnordered
 * @template ValueType
 * @extends TreenodeValueObjectAbstract<ValueType>
 * @implements TreenodeValueObjectUnorderedInterface<ValueType>
 */
class TreenodeValueObjectUnordered extends TreenodeValueObjectAbstract implements TreenodeValueObjectUnorderedInterface
{
    /**
     * hydrateFromNode
     * @param TreenodeUnorderedInterface<ValueType> $node
     */
    public function hydrateFromNode(TreenodeUnorderedInterface $node): void
    {
        $this->nodeId = $node->getNodeId();
        $this->parentId = $node->getParentId();
        $this->treeId = $node->getTreeId();
        $this->value = $node->getValue();
    }

    /**
     * hydrateFromArray
     * @param array{
     *      nodeId: non-negative-int,
     *      parentId: non-negative-int|null,
     *      treeId: non-negative-int,
     *      value: ValueType
     *  } $nodeData
     */
    public function hydrateFromAssociativeArray(array $nodeData): void
    {
        $this->nodeId = $nodeData['nodeId'];
        $this->parentId = $nodeData['parentId'];
        $this->treeId = $nodeData['treeId'];
        $this->value = $nodeData['value'];
    }

    /**
     * hydrateFromNumericArray
     * @param array{
     *       0: non-negative-int,
     *       1: non-negative-int|null,
     *       2: non-negative-int,
     *       3: ValueType
     *   } $nodeData
     */
    public function hydrateFromNumericArray(array $nodeData): void
    {
        $this->nodeId = $nodeData[0];
        $this->parentId = $nodeData[1];
        $this->treeId = $nodeData[2];
        $this->value = $nodeData[3];
    }
}
