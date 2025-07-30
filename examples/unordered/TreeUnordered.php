<?php

namespace pvcExamples\struct\unordered;

use pvc\struct\collection\Collection;
use pvc\struct\tree\tree\Tree;

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