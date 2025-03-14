<?php

declare (strict_types = 1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * @template PayloadType
 * @implements TreenodeCollectionFactoryInterface<PayloadType>
 */
class TreenodeCollectionFactory implements TreenodeCollectionFactoryInterface
{
    /**
     * @param CollectionFactoryInterface<TreenodeInterface<PayloadType>> $collectionFactory
     */
    public function __construct(
        protected CollectionFactoryInterface $collectionFactory)
    {
    }

    /**
     * @param array<TreenodeInterface<PayloadType>> $treenodes
     * @return TreenodeCollection<PayloadType>
     */
    public function makeTreenodeCollection(array $treenodes = []): TreenodeCollection
    {
        return new TreenodeCollection($this->collectionFactory->makeCollection($treenodes));
    }
}