<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\err\InvalidNodeIdException;

/**
 * Class TreenodeOrdered
 *
 * @template PayloadType of HasPayloadInterface
 * @extends TreenodeAbstract<PayloadType, TreenodeOrderedInterface, TreeOrderedInterface, CollectionOrderedInterface>
 * @implements TreenodeOrderedInterface<PayloadType>
 */
class TreenodeOrdered extends TreenodeAbstract implements TreenodeOrderedInterface
{
    /**
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @param non-negative-int $treeId
     * @param non-negative-int $index
     * @param TreeOrderedInterface<PayloadType> $tree
     * @param CollectionOrderedInterface<TreenodeOrderedInterface<PayloadType>> $collectionOrdered
     * @throws InvalidNodeIdException
     */
    public function __construct(
        int $nodeId,
        ?int $parentId,
        int $treeId,
        int $index,
        TreeOrderedInterface $tree,
        CollectionOrderedInterface $collectionOrdered
    ) {
        parent::__construct($nodeId, $parentId, $treeId, $tree, $collectionOrdered);
        $this->setIndex($index);
    }

    /**
     * @function getIndex returns the position of this node in its list of siblings
     * @return non-negative-int
     */
    public function getIndex(): int
    {
        /** @var CollectionOrderedInterface<TreenodeOrderedInterface<PayloadType>> $siblings */
        $siblings = $this->getSiblings();
        /** @var non-negative-int $key */
        $key = $siblings->getKey($this);
        return $siblings->getIndex($key);
    }

    /**
     * changes this node's position in the child list of the parent.
     *
     * @function setIndex
     * @param non-negative-int $index
     */
    public function setIndex(int $index): void
    {
        /** @var CollectionOrderedInterface<TreenodeOrderedInterface<PayloadType>> $siblings */
        $siblings = $this->getSiblings();
        /** @var non-negative-int $key */
        $key = $siblings->getKey($this);
        $siblings->setIndex($key, $index);
    }
}
