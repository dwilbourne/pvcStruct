<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node\factory;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\collection\CollectionAbstractInterface as CollectionType;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\payload\PayloadTesterTrait;
use pvc\struct\tree\node\TreenodeAbstract;

/**
 * Class TreenodeAbstractFactory
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeDTOInterface
 * @implements TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
abstract class TreenodeAbstractFactory implements TreenodeFactoryInterface
{
    /**
     * @use PayloadTesterTrait<PayloadType>
     */
    use PayloadTesterTrait;

    /**
     * @var CollectionFactoryInterface<PayloadType, CollectionType> $collectionFactory
     */
    protected CollectionFactoryInterface $collectionFactory;

    /**
     * @var TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected TreeAbstractInterface $tree;

    /**
     * @param CollectionFactoryInterface<PayloadType, CollectionType> $collectionFactory
     * @param PayloadTesterInterface<PayloadType> $tester
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        PayloadTesterInterface $tester = null
    ) {
        $this->setCollectionFactory($collectionFactory);
        $this->setPayloadTester($tester);
    }

    /**
     * @return CollectionFactoryInterface<PayloadType, CollectionType>
     */
    public function getCollectionFactory(): CollectionFactoryInterface
    {
        return $this->collectionFactory;
    }

    /**
     * setCollectionFactory
     * @param CollectionFactoryInterface<PayloadType, CollectionType> $collectionFactory
     */
    public function setCollectionFactory(CollectionFactoryInterface $collectionFactory): void
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * getTree
     * @return TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getTree(): TreeAbstractInterface
    {
        return $this->tree;
    }

    /**
     * setTree
     * @param TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $tree
     */
    public function setTree(TreeAbstractInterface $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * makeNode
     * @return TreenodeAbstract<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    abstract public function makeNode(): TreenodeAbstract;
}
