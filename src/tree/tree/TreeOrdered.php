<?php

declare(strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;

/**
 * Class TreeOrdered
 *
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore -- extra line length needed for type checker to recognize the generics properly
 * @extends TreeAbstract<PayloadType, TreenodeOrderedInterface, TreeOrderedInterface, TreenodeDTOOrderedInterface, CollectionOrderedInterface>
 * @implements TreeOrderedInterface<PayloadType>
 */
class TreeOrdered extends TreeAbstract implements TreeOrderedInterface
{
    /**
     * sortChildValueObjects
     * @param array<TreenodeDTOOrderedInterface<PayloadType>> $childValueObjects
     * @return bool
     */
    protected function sortChildValueObjects(array &$childValueObjects): bool
    {
        /**
         * @param TreenodeDTOOrderedInterface<PayloadType> $a
         * @param TreenodeDTOOrderedInterface<PayloadType> $b
         * @return int<-1, 1>
         */
        $callback = function (TreenodeDTOOrderedInterface $a, TreenodeDTOOrderedInterface $b): int {
            return ($a->index <=> $b->index);
        };
        $result = uasort($childValueObjects, $callback);
        return $result;
    }
}
