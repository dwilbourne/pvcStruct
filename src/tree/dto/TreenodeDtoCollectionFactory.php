<?php

namespace pvc\struct\tree\dto;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoCollectionFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;

/**
 * @template PayloadType
 * @implements TreenodeDtoCollectionFactoryInterface<PayloadType>
 */
class TreenodeDtoCollectionFactory implements TreenodeDtoCollectionFactoryInterface
{
    /**
     * @param CollectionFactoryInterface<TreenodeDtoInterface<PayloadType>> $collectionFactory
     */
    public function __construct(protected CollectionFactoryInterface $collectionFactory)
    {
    }

    /**
     * @param array<TreenodeDtoInterface<PayloadType>> $dtoArray
     * @return TreenodeDtoCollection<PayloadType>
     */
    public function makeTreenodeDtoCollection(array $dtoArray = []): TreenodeDtoCollection
    {
        /**
         * @return TreenodeDtoCollection<PayloadType, TreenodeDtoInterface<PayloadType>>
         */
        return new TreenodeDtoCollection($this->collectionFactory->makeCollection($dtoArray));
    }
}