<?php

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\collection\Collection;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\tree\TreeUnordered;

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