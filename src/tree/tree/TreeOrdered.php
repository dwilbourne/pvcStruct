<?php

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeOrdered;

/**
 * @class TreeOrdered
 * @template PayloadType
 * @extends Tree<PayloadType, TreenodeOrdered, TreeOrdered, CollectionOrdered>
 * @phpstan-import-type TreenodeOrderedDtoShape from TreenodeOrdered
 */
class TreeOrdered extends Tree
{
    /**
     * @param TreenodeFactoryOrdered<PayloadType> $treenodeFactory
     */
    public function __construct(
        TreenodeFactoryOrdered $treenodeFactory,
    )
    {
        $this->treenodeDtoComparator = function (mixed $a, mixed $b) {
            /**
             * @var TreenodeOrderedDtoShape $a
             * @var TreenodeOrderedDtoShape $b
             */
            return $a->index <=> $b->index;
        };
        parent::__construct($treenodeFactory);
    }
}