<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node_value_object;

use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectOrderedInterface;

/**
 * Class TreenodeValueObjectOrdered
 * @template ValueType
 * @extends TreenodeValueObjectAbstract<ValueType>
 * @implements TreenodeValueObjectOrderedInterface<ValueType>
 */
class TreenodeValueObjectOrdered extends TreenodeValueObjectAbstract implements TreenodeValueObjectOrderedInterface
{
    /**
     * @var non-negative-int
     */
    protected int $index;

    /**
     * hydrateFromNode
     * @param TreenodeOrderedInterface<ValueType> $node
     */
    public function hydrateFromNode(TreenodeOrderedInterface $node): void
    {
        $this->nodeId = $node->getNodeId();
        $this->parentId = $node->getParentId();
        $this->treeId = $node->getTreeId();
        $this->value = $node->getValue();
        $this->index = $node->getIndex();
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
     *     'value': ValueType,
     *     'index': non-negative-int
     * } $nodeData
     */
    public function hydrateFromAssociativeArray(array $nodeData): void
    {
        $this->nodeId = $nodeData['nodeId'];
        $this->parentId = $nodeData['parentId'];
        $this->treeId = $nodeData['treeId'];
        $this->value = $nodeData['value'];
        $this->index = $nodeData['index'];
    }

    /**
     * hydrateFromNumericArray
     * @param array{
     *        0: non-negative-int,
     *        1: non-negative-int|null,
     *        2: non-negative-int,
     *        3: ValueType,
     *        4: non-negative-int,
     *    } $nodeData
     */
    public function hydrateFromNumericArray(array $nodeData): void
    {
        $this->nodeId = $nodeData[0];
        $this->parentId = $nodeData[1];
        $this->treeId = $nodeData[2];
        $this->value = $nodeData[3];
        $this->index = $nodeData[4];
    }
}
