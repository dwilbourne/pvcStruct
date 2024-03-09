<?php

declare(strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;

/**
 * Class TreeOrdered
 *
 * @template ValueType
 * @phpcs:ignore -- extra line length needed for type checker to recognize the generics properly
 * @extends TreeAbstract<ValueType, TreenodeOrderedInterface, TreeOrderedInterface, TreenodeValueObjectOrderedInterface, CollectionOrderedInterface>
 * @implements TreeOrderedInterface<ValueType>
 */
class TreeOrdered extends TreeAbstract implements TreeOrderedInterface
{
    /**
     * sortChildValueObjects
     * @param array<TreenodeValueObjectOrderedInterface<ValueType>> $childValueObjects
     * @return bool
     */
    protected function sortChildValueObjects(array &$childValueObjects): bool
    {
        /**
         * @param TreenodeValueObjectOrderedInterface<ValueType> $a
         * @param TreenodeValueObjectOrderedInterface<ValueType> $b
         * @return int<-1, 1>
         */
        $callback = function (TreenodeValueObjectOrderedInterface $a, TreenodeValueObjectOrderedInterface $b): int {
            return ($a->getIndex() <=> $b->getIndex());
        };
        $result = uasort($childValueObjects, $callback);
        return $result;
    }
}
