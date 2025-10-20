<?php

namespace pvcTests\struct\integration_tests\fixture\tree;

use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;

/**
 * @implements TreenodeCollectionFactoryInterface<TreenodeCollection>
 */
class TreenodeCollectionFactory implements TreenodeCollectionFactoryInterface
{
    public function makeCollection()
    {
        return new TreenodeCollection();
    }
}