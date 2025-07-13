<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;

/**
 * Class TreenodeFactory
 *
 * Tree and TreenodeFactory are mutually dependent.  The constructors are set up so that you create
 * TreenodeFactory first without its tree dependency, use TreenodeFactory in the construction of a new tree,
 * and then go back and set the tree property in TreenodeFactory.  Tree factory does all this in the method
 * makeTree.
 *
 * @template TreenodeType of TreenodeInterface
 * @template CollectionType of CollectionInterface
 * @implements TreenodeFactoryInterface<TreenodeType, CollectionType>
 */
abstract class TreenodeFactory implements TreenodeFactoryInterface
{
    /**
     * @var TreeInterface<TreenodeType, CollectionType>
     */
    protected TreeInterface $tree;

    /**
     * @param CollectionFactoryInterface<TreenodeType, CollectionType> $treenodeCollectionFactory
     */
    public function __construct(
        protected CollectionFactoryInterface $treenodeCollectionFactory,
    ) {
    }

    /**
     * @param TreeInterface<TreenodeType, CollectionType> $tree
     * @return void
     */
    public function setTree(TreeInterface $tree): void
    {
        $this->tree = $tree;
    }

    public function getTree(): TreeInterface
    {
        return $this->tree;
    }

    /**
     * @return CollectionFactoryInterface<TreenodeType, CollectionType>
     */
    public function getTreenodeCollectionFactory(): CollectionFactoryInterface
    {
        return $this->treenodeCollectionFactory;
    }
}
