<?php

namespace pvc\struct\tree\node;

use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\tree\TreeOrdered;

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