<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\factory;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\factory\NodeTypeFactoryInterface;
use pvc\interfaces\struct\tree\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\interfaces\validator\ValidatorInterface;

/**
 * Class TreenodeAbstractFactory
 * @template ValueType
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements TreenodeFactoryInterface<ValueType, NodeType, CollectionType, TreeType>
 */
class TreenodeAbstractFactory implements TreenodeFactoryInterface
{
    /**
     * @var CollectionFactoryInterface<CollectionType> $collectionFactory
     */
    protected CollectionFactoryInterface $collectionFactory;

    /**
     * @var NodeTypeFactoryInterface<ValueType, NodeType, TreeType, CollectionType>
     */
    protected NodeTypeFactoryInterface $nodeTypeFactory;

    /**
     * @var ValidatorInterface $validator
     */
    protected ValidatorInterface $validator;

    /**
     * @var TreeType
     */
    protected TreeAbstractInterface $tree;

    /**
     * @param NodeTypeFactoryInterface<ValueType, NodeType, TreeType, CollectionType> $nodeTypeFactory
     * @param CollectionFactoryInterface<CollectionType> $collectionFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(
        NodeTypeFactoryInterface $nodeTypeFactory,
        CollectionFactoryInterface $collectionFactory,
        ValidatorInterface $validator
    ) {
        $this->nodeTypeFactory = $nodeTypeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->validator = $validator;
    }

    /**
     * @return CollectionFactoryInterface<CollectionType>
     */
    public function getCollectionFactory(): CollectionFactoryInterface
    {
        return $this->collectionFactory;
    }

    /**
     * getTree
     * @return TreeType
     */
    public function getTree(): TreeAbstractInterface
    {
        return $this->tree;
    }

    /**
     * setTree
     * @param TreeType $tree
     */
    public function setTree(TreeAbstractInterface $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * makeNode
     * @param TreenodeValueObjectInterface<ValueType> $valueObject
     * @return NodeType
     */
    public function makeNode(TreenodeValueObjectInterface $valueObject): TreenodeAbstractInterface
    {
        /** @var CollectionType $collection */
        $collection = $this->collectionFactory->makeCollection();

        /** @var NodeType $node */
        $node = $this->getNodeTypeFactory()->makeNodeType($valueObject, $this->tree, $collection);

        $node->setValueValidator($this->GetValueValidator());
        $node->setValue($valueObject->getValue());
        return $node;
    }

    /**
     * makeCollection
     * @return CollectionType<NodeType>
     */
    public function makeCollection(): CollectionAbstractInterface
    {
        return $this->collectionFactory->makeCollection();
    }

    /**
     * @return NodeTypeFactoryInterface<ValueType, NodeType, TreeType, CollectionType>
     */
    public function getNodeTypeFactory(): NodeTypeFactoryInterface
    {
        return $this->nodeTypeFactory;
    }

    /**
     * @return ValidatorInterface
     */
    public function GetValueValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
