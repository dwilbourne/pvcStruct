<?php

namespace pvc\struct\tree\tree;

use pvc\struct\collection\Collection;
use pvc\struct\tree\node\TreenodeFactoryUnordered;
use pvc\struct\tree\node\TreenodeUnordered;

/**
 * @class TreeOrdered
 * @extends Tree<TreenodeUnordered, Collection, TreeUnordered>
 */
class TreeUnordered extends Tree
{
    /**
     * @param  TreenodeFactoryUnordered  $treenodeFactory
     */
    public function __construct(
        TreenodeFactoryUnordered $treenodeFactory,
    ) {
        parent::__construct($treenodeFactory);
    }

}