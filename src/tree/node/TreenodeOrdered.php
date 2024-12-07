<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;

/**
 * Class TreenodeOrdered
 *
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore
 * @extends TreenodeAbstract<PayloadType, TreenodeOrderedInterface, TreeOrderedInterface, CollectionOrderedInterface, TreenodeDTOOrderedInterface>
 * @implements TreenodeOrderedInterface<PayloadType>
 */
class TreenodeOrdered extends TreenodeAbstract implements TreenodeOrderedInterface
{
    /**
     * hydrate
     * @param TreenodeDTOOrderedInterface<PayloadType> $dto
     * @param TreeOrderedInterface<PayloadType> $tree
     */
    public function hydrate(TreenodeDTOInterface $dto, TreeAbstractInterface $tree): void
    {
        parent::hydrate($dto, $tree);
        $this->setIndex($dto->index);
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
