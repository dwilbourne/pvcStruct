<?php

namespace pvc\struct\tree\node;

use pvc\struct\collection\Collection;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\tree\TreeUnordered;

/**
 * @template PayloadType
 * @extends TreenodeFactory<PayloadType, TreenodeUnordered, TreeUnordered, Collection>
 */
class TreenodeFactoryUnordered extends TreenodeFactory
{
    /**
     * @return TreenodeUnordered<PayloadType>
     * @throws ChildCollectionException|TreenodeFactoryNotInitializedException
     */
    public function makeNode(): TreenodeUnordered
    {
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }
        /** @var Collection<TreenodeUnordered<PayloadType>> $treenodeCollection */
        $treenodeCollection = $this->treenodeCollectionFactory->makeCollection([]);
        /** @var TreenodeUnordered<PayloadType> $node */
        $node = new TreenodeUnordered($treenodeCollection, $this->tree);
        return $node;
    }
}