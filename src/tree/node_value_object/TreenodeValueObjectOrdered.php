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
        $this->setNodeId($node->getNodeId());
        $this->setParentId($node->getParentId());
        $this->setTreeId($node->getTreeId());
        $this->setValue($node->getValue());
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
     *     'value': ValueType,
     *     'index': non-negative-int
     * } $nodeData
     *
     * phpstan does not like the fact that the shape of the argument in the parent class is a subset of the shape of
     * the argument in this class.....
     * @phpstan-ignore argument.type
     */
    public function hydrateFromAssociativeArray(array $nodeData): void
    {
        parent::hydrateFromAssociativeArray($nodeData);
        $this->setIndex($nodeData['index']);
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
     *
     *  phpstan does not like the fact that the shape of the argument in the parent class is a subset of the shape of
     *  the argument in this class.....
     * @phpstan-ignore argument.type
     */
    public function hydrateFromNumericArray(array $nodeData): void
    {
        parent::hydrateFromNumericArray($nodeData);
        $this->setIndex($nodeData[4]);
    }
}
