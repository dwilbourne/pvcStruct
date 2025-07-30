<?php

namespace pvcExamples\struct\ordered;

use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\node\TreenodeFactory;

/**
 * @extends TreenodeFactory<TreenodeOrdered, CollectionOrdered, TreeOrdered>
 */
class TreenodeFactoryOrdered extends TreenodeFactory
{
    /**
     * @return TreenodeOrdered
     * @throws ChildCollectionException|TreenodeFactoryNotInitializedException
     */
    public function makeNode(): TreenodeOrdered
    {
        /** @var CollectionOrdered<TreenodeOrdered> $treenodeCollection */
        $treenodeCollection = $this->collectionFactory->makeCollection([]);

        return new TreenodeOrdered($treenodeCollection);
    }

}