<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\factory;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\payload\HasPayloadValidatorInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\interfaces\struct\tree\factory\NodeTypeFactoryInterface;
use pvc\interfaces\struct\tree\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\payload\PayloadValidatorTrait;

/**
 * Class TreenodeAbstractFactory
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements TreenodeFactoryInterface<PayloadType, NodeType, CollectionType, TreeType>
 */
class TreenodeAbstractFactory implements TreenodeFactoryInterface
{
    /**
     * @use PayloadValidatorTrait<PayloadType>
     */
    use PayloadValidatorTrait;

    /**
     * @var CollectionFactoryInterface<CollectionType> $collectionFactory
     */
    protected CollectionFactoryInterface $collectionFactory;

    /**
     * @var NodeTypeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    protected NodeTypeFactoryInterface $nodeTypeFactory;

    /**
     * @var HasPayloadValidatorInterface<PayloadType> $validator
     */

    /**
     * @var TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    protected TreeAbstractInterface $tree;

    /**
     * @param NodeTypeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType> $nodeTypeFactory
     * @param CollectionFactoryInterface<CollectionType> $collectionFactory
     * @param ValidatorPayloadInterface<PayloadType>|null $validator
     */
    public function __construct(
        NodeTypeFactoryInterface $nodeTypeFactory,
        CollectionFactoryInterface $collectionFactory,
        ValidatorPayloadInterface $validator = null
    ) {
        $this->setNodeTypeFactory($nodeTypeFactory);
        $this->setCollectionFactory($collectionFactory);
        if ($validator) {
            $this->setPayloadValidator($validator);
        }
    }

    /**
     * @return CollectionFactoryInterface<CollectionType>
     */
    public function getCollectionFactory(): CollectionFactoryInterface
    {
        return $this->collectionFactory;
    }

    /**
     * setCollectionFactory
     * @param CollectionFactoryInterface<CollectionType> $collectionFactory
     */
    public function setCollectionFactory(CollectionFactoryInterface $collectionFactory): void
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * getTree
     * @return TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    public function getTree(): TreeAbstractInterface
    {
        return $this->tree;
    }

    /**
     * setTree
     * @param TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType> $tree
     */
    public function setTree(TreeAbstractInterface $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * @return NodeTypeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    public function getNodeTypeFactory(): NodeTypeFactoryInterface
    {
        return $this->nodeTypeFactory;
    }

    /**
     * setNodeTypeFactory
     * @param NodeTypeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType> $nodeTypeFactory
     */
    public function setNodeTypeFactory(NodeTypeFactoryInterface $nodeTypeFactory): void
    {
        $this->nodeTypeFactory = $nodeTypeFactory;
    }

    /**
     * makeCollection
     * @return CollectionAbstractInterface<PayloadType, NodeType>
     */
    public function makeCollection(): CollectionAbstractInterface
    {
        return $this->collectionFactory->makeCollection();
    }

    /**
     * makeNode
     * @param TreenodeValueObjectInterface<PayloadType> $valueObject
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType>
     */
    public function makeNode(TreenodeValueObjectInterface $valueObject): TreenodeAbstractInterface
    {
        /** @var CollectionType $collection */
        $collection = $this->collectionFactory->makeCollection();

        /** @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType> $node */
        $node = $this->getNodeTypeFactory()->makeNodeType($valueObject, $this->tree, $collection);
        if ($this->getPayloadValidator()) {
            $node->setPayloadValidator($this->getPayloadValidator());
        }
        $node->setPayload($valueObject->getPayload());
        return $node;
    }
}
