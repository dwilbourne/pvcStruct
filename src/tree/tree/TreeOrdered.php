<?php

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeOrdered;

/**
 * @class TreeOrdered
 * @extends Tree<TreenodeOrdered, CollectionOrdered>
 * @phpstan-import-type TreenodeDtoShape from TreenodeInterface
 */
class TreeOrdered extends Tree
{
    /**
     * @param TreenodeFactoryOrdered $treenodeFactory
     */
    public function __construct(
        TreenodeFactoryOrdered $treenodeFactory,
    )
    {
        $this->treenodeDtoComparator = function ($a, $b) {
            /**
             * @var TreenodeDtoShape $a
             * @var TreenodeDtoShape $b
             */
            assert(isset($a->index));
            assert(isset($b->index));
           return $a->index <=> $b->index;
        };
        parent::__construct($treenodeFactory);
    }
}