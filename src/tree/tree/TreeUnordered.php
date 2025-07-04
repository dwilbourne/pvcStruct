<?php

namespace pvc\struct\tree\tree;

use pvc\struct\collection\Collection;
use pvc\struct\tree\node\TreenodeFactoryUnordered;
use pvc\struct\tree\node\TreenodeUnordered;

/**
 * @class TreeOrdered
 * @template PayloadType
 * @extends Tree<PayloadType, TreenodeUnordered, TreeUnordered, Collection>
 */
class TreeUnordered extends Tree
{
    /**
     * @param TreenodeFactoryUnordered<PayloadType> $treenodeFactory
     */
    public function __construct(
        TreenodeFactoryUnordered $treenodeFactory,
    )
    {
        parent::__construct($treenodeFactory);
    }

}