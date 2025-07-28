<?php

namespace pvc\struct\tree\tree;

use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\dto\TreenodeDtoOrdered;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeOrdered;

/**
 * @class TreeOrdered
 * @extends Tree<TreenodeOrdered, CollectionOrdered, TreeOrdered>
 */
class TreeOrdered extends Tree
{
    public function __construct(
        TreenodeFactoryOrdered $treenodeFactory,
    ) {
        $this->treenodeComparator = function (
            TreenodeDtoOrdered $a,
            TreenodeDtoOrdered $b
        ) {
            return $a->getIndex() <=> $b->getIndex();
        };
        parent::__construct($treenodeFactory);
    }
}