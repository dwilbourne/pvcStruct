<?php

namespace pvcExamples\struct\ordered;

use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\dto\TreenodeDtoOrdered;
use pvc\struct\tree\tree\Tree;

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