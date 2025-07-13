<?php

namespace pvc\struct\tree\node;

use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;

/**
 * @extends TreenodeFactory<TreenodeOrdered, CollectionOrdered>
 */
class TreenodeFactoryOrdered extends TreenodeFactory
{
    /**
     * @return TreenodeOrdered
     * @throws ChildCollectionException|TreenodeFactoryNotInitializedException
     */
    public function makeNode(): TreenodeOrdered
    {
        if (!isset($this->tree)) {
            throw new TreeNodeFactoryNotInitializedException();
        }

        /** @var CollectionOrdered<TreenodeOrdered> $treenodeCollection */
        $treenodeCollection = $this->treenodeCollectionFactory->makeCollection([]);

        return new TreenodeOrdered($treenodeCollection, $this->tree);
    }

}