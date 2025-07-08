<?php

namespace pvc\struct\tree\node;

use pvc\struct\collection\Collection;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;

/**
 * @extends TreenodeFactory<TreenodeUnordered, Collection>
 */
class TreenodeFactoryUnordered extends TreenodeFactory
{
    /**
     * @return TreenodeUnordered
     * @throws ChildCollectionException|TreenodeFactoryNotInitializedException
     */
    public function makeNode(): TreenodeUnordered
    {
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }
        /** @var Collection<TreenodeUnordered> $treenodeCollection */
        $treenodeCollection = $this->treenodeCollectionFactory->makeCollection([]);

        return new TreenodeUnordered($treenodeCollection, $this->tree);
    }
}