<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\err\TreenodeFactoryNotInitializedException;

/**
 * Class TreenodeFactory
 * @template PayloadType
 * @implements TreenodeFactoryInterface<PayloadType>
 *
 * Tree and TreenodeFactory are mutually dependent.  The constructors are set up so that you create
 * TreenodeFactory first without its tree dependency, use TreenodeFactory in the construction of a new tree,
 * and then go back and set the tree property in TreenodeFactory.  Tree factory does all this in the method
 * makeTree.
 */
class TreenodeFactory implements TreenodeFactoryInterface
{
    /**
     * @var TreeInterface<PayloadType>
     */
    protected TreeInterface $tree;

    /**
     * @param TreenodeCollectionFactoryInterface<PayloadType> $treenodeCollectionFactory
     * @param ValTesterInterface<PayloadType>|null $payloadTester
     */
    public function __construct(
        protected TreenodeCollectionFactoryInterface $treenodeCollectionFactory,
        protected ?ValTesterInterface                $payloadTester = null
    ) {
    }

    public function isInitialized(): bool
    {
        return isset($this->tree);
    }

    /**
     * @param TreeInterface<PayloadType> $tree
     * @return void
     */
    public function initialize(TreeInterface $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * @return TreenodeCollectionFactoryInterface<PayloadType>
     */
    public function getTreenodeCollectionFactory(): TreenodeCollectionFactoryInterface
    {
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }
        return $this->treenodeCollectionFactory;
    }

    /**
     * @return TreenodeInterface<PayloadType>
     * @throws ChildCollectionException|TreenodeFactoryNotInitializedException
     */
    public function makeNode(): TreenodeInterface
    {
        if (!$this->isInitialized()) {
            throw new TreeNodeFactoryNotInitializedException();
        }
        /** @var TreenodeCollection<covariant PayloadType> $treenodeCollection */
        $treenodeCollection = $this->treenodeCollectionFactory->makeTreenodeCollection();
        return new Treenode($treenodeCollection, $this->tree, $this->payloadTester);
    }
}
