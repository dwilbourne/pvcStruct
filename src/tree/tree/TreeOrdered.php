<?php

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * @class TreeOrdered
 * @template PayloadType
 * @extends Tree<PayloadType>
 * @phpstan-import-type TreenodeDtoShape from TreenodeInterface
 */
class TreeOrdered extends Tree
{
    /**
     * @param TreenodeFactoryInterface<PayloadType> $treenodeFactory
     */
    public function __construct(
        TreenodeFactoryInterface $treenodeFactory,
    )
    {
        $this->treenodeDtoComparator = function (mixed $a, mixed $b) {
            /**
             * @var TreenodeDtoShape $a
             * @var TreenodeDtoShape $b
             */
            return $a->index <=> $b->index;
        };
        parent::__construct($treenodeFactory);
    }
}