<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeOrderedFactoryInterface;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\tree\TreeOrdered;

/**
 * Class TreeOrderedFactory
 * @template PayloadType of HasPayloadInterface
 */
class TreeOrderedFactory
{
    /**
     * @param TreenodeOrderedFactoryInterface<PayloadType> $treenodeFactory
     */
    public function __construct(protected TreenodeOrderedFactoryInterface $treenodeFactory)
    {
    }

    /**
     * makeTree
     * @param int $treeId
     * @return TreeOrdered<PayloadType>
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function makeTree(int $treeId)
    {
        return new TreeOrdered($treeId, $this->treenodeFactory);
    }
}
