<?php

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
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
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }

        /** @var CollectionOrdered<TreenodeOrdered> $treenodeCollection */
        $treenodeCollection = $this->treenodeCollectionFactory->makeCollection([]);

        /**
         * at the moment, I am not experienced enough to understand why the tree needs to be type hinted below. Without
         * the type hint, phpstan complains about the tree argument and refers to the template-covariant documentation.
         * TODO: in the phpstan sandbox, understand why it thinks the tree is covariant (in a contravariant position)
         */
        /** @var TreeInterface<TreenodeOrderedInterface, CollectionOrderedInterface<TreenodeOrderedInterface>> $tree */
        $tree = $this->tree;

        return new TreenodeOrdered($treenodeCollection, $tree);
    }

}