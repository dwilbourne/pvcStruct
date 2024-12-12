<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeUnorderedFactoryInterface;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\tree\TreeUnordered;

/**
 * Class TreeUnorderedFactory
 * @template PayloadType of HasPayloadInterface
 */
class TreeUnorderedFactory
{
    /**
     * @param TreenodeUnorderedFactoryInterface<PayloadType> $treenodeFactory
     */
    public function __construct(protected TreenodeUnorderedFactoryInterface $treenodeFactory)
    {
    }

    /**
     * makeTree
     * @param int $treeId
     * @return TreeUnordered<PayloadType>
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function makeTree(int $treeId): TreeUnordered
    {
        return new TreeUnordered($treeId, $this->treenodeFactory);
    }
}
