<?php

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\tree\TreeOrdered;

/**
 * @template PayloadType
 * @extends TreenodeFactory<PayloadType, TreenodeOrdered, CollectionOrdered>
 */
class TreenodeFactoryOrdered extends TreenodeFactory
{
    /**
     * @return TreenodeOrdered<PayloadType>
     * @throws ChildCollectionException|TreenodeFactoryNotInitializedException
     */
    public function makeNode(): TreenodeOrdered
    {
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }
        /** @var CollectionOrdered<TreenodeOrdered<PayloadType>> $treenodeCollection */
        $treenodeCollection = $this->treenodeCollectionFactory->makeCollection([]);
        /** @var TreenodeOrdered<PayloadType> $node */
        $node = new TreenodeOrdered($treenodeCollection, $this->tree);
        return $node;
    }

}