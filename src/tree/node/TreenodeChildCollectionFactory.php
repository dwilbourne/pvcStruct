<?php

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * @template TreenodeType of TreenodeInterface
 * @implements TreenodeChildCollectionFactoryInterface<TreenodeType>
 */
class TreenodeChildCollectionFactory implements TreenodeChildCollectionFactoryInterface
{

    /**
     * @return TreenodeChildCollectionInterface<TreenodeType>
     */
    public function makeChildCollection(): TreenodeChildCollectionInterface
    {
        return new TreenodeChildCollection();
    }
}