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
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;
use pvc\struct\tree\tree\Tree;

/**
 * Class TreenodeFactory
 *
 * Tree and TreenodeFactory are mutually dependent.  The constructors are set up so that you create
 * TreenodeFactory first without its tree dependency, use TreenodeFactory in the construction of a new tree,
 * and then go back and set the tree property in TreenodeFactory.  Tree factory does all this in the method
 * makeTree.
 *
 * @template PayloadType
 * @template TreenodeType of TreenodeInterface
 * @template TreeType of TreeInterface
 * @template CollectionType of CollectionInterface
 * @implements TreenodeFactoryInterface<PayloadType, TreenodeType, TreeType, CollectionType>
 */
abstract class TreenodeFactory implements TreenodeFactoryInterface
{
    /**
     * @var TreeType
     */
    protected TreeInterface $tree;

    /**
     * @param CollectionFactoryInterface<TreenodeType, CollectionType> $treenodeCollectionFactory
     */
    public function __construct(
        protected CollectionFactoryInterface $treenodeCollectionFactory,
    ) {
    }

    public function isInitialized(): bool
    {
        return isset($this->tree);
    }

    /**
     * @param TreeType $tree
     * @return void
     */
    public function initialize(TreeInterface $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * @return CollectionFactoryInterface<TreenodeType, CollectionType>
     */
    public function getTreenodeCollectionFactory(): CollectionFactoryInterface
    {
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }
        return $this->treenodeCollectionFactory;
    }
}
