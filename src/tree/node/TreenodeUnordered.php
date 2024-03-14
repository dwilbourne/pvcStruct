<?php

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\struct\tree\err\InvalidNodeIdException;

/**
 * class TreenodeUnordered
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore -- generics must be all on the same line in order to be processed correctly by phpstan
 * @extends TreenodeAbstract<PayloadType, TreenodeUnorderedInterface, TreeUnorderedInterface, CollectionUnorderedInterface>
 * @implements TreenodeUnorderedInterface<PayloadType>
 */
class TreenodeUnordered extends TreenodeAbstract implements TreenodeUnorderedInterface
{
    /**
     * @param non-negative-int $nodeId
     * @param non-negative-int|null $parentId
     * @param non-negative-int $treeId
     * @param TreeUnorderedInterface<PayloadType> $tree
     * @param CollectionUnorderedInterface<TreenodeUnorderedInterface<PayloadType>> $collectionUnordered
     * @throws InvalidNodeIdException
     */
    public function __construct(
        int $nodeId,
        ?int $parentId,
        int $treeId,
        TreeUnorderedInterface $tree,
        CollectionUnorderedInterface $collectionUnordered
    ) {
        parent::__construct($nodeId, $parentId, $treeId, $tree, $collectionUnordered);
    }
}
