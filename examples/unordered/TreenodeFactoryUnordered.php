<?php

namespace pvcExamples\struct\unordered;

use pvc\struct\collection\Collection;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\node\TreenodeFactory;

/**
 * @extends TreenodeFactory<TreenodeUnordered, Collection, TreeUnordered>
 */
class TreenodeFactoryUnordered extends TreenodeFactory
{
    /**
     * @return TreenodeUnordered
     * @throws ChildCollectionException
     */
    public function makeNode(): TreenodeUnordered
    {
        /** @var Collection<TreenodeUnordered> $treenodeCollection */
        $treenodeCollection = $this->collectionFactory->makeCollection([]);

        return new TreenodeUnordered($treenodeCollection);
    }
}