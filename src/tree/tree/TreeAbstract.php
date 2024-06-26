<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\events\TreeAbstractEventHandlerInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\SetTreeIdException;
use pvc\struct\tree\err\TreeNotEmptyHydrationException;

/**
 * @class TreeAbstract
 * @template PayloadType of HasPayloadInterface
 * @template NodeType of TreenodeAbstractInterface
 * @template TreeType of TreeAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template ValueObjectType of TreenodeValueObjectInterface
 * @implements TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
 */
abstract class TreeAbstract implements TreeAbstractInterface
{
    /**
     * @var int
     */
    protected int $treeid;

    /**
     * @var TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $treenodeFactory
     */
    protected TreenodeFactoryInterface $treenodeFactory;

    /**
     * @var TreeAbstractEventHandlerInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    protected TreeAbstractEventHandlerInterface $eventHandler;

    /**
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
     */
    protected $root;

    /**
     * @var array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>>
     */
    protected array $nodes = [];

    /**
     * @param int $treeid
     * @phpcs:ignore
     * @param TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $treenodeFactory
     * @phpcs:ignore
     * @param TreeAbstractEventHandlerInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $handler
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function __construct(
        int $treeid,
        TreenodeFactoryInterface $treenodeFactory,
        TreeAbstractEventHandlerInterface $handler
    ) {
        $this->setTreeId($treeid);
        $this->setTreenodeFactory($treenodeFactory);
        $this->setEventHandler($handler);
    }

    /**
     * validateTreeId
     *
     * all tree ids are integers >= 0
     *
     * @param int $nodeid
     * @return bool
     */
    public function validateTreeId(int $nodeid): bool
    {
        return 0 <= $nodeid;
    }

    /**
     * @function getTreeId
     * @return int
     */
    public function getTreeId(): int
    {
        return $this->treeid;
    }

    /**
     * @function setTreeId
     * @param int $treeId
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function setTreeId(int $treeId): void
    {
        /**
         * treeid must pass validation
         */
        if (!$this->validateTreeId($treeId)) {
            throw new InvalidTreeidException($treeId);
        }

        /**
         * treeid can only be changed if the tree is empty
         */
        if (!$this->isEmpty()) {
            throw new SetTreeIdException();
        }
        $this->treeid = $treeId;
    }


    /**
     * @return TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getTreenodeFactory(): TreenodeFactoryInterface
    {
        return $this->treenodeFactory;
    }

    /**
     * @phpcs:ignore
     * @param TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $treenodeFactory
     */
    public function setTreenodeFactory(TreenodeFactoryInterface $treenodeFactory): void
    {
        $treenodeFactory->setTree($this);
        $this->treenodeFactory = $treenodeFactory;
    }

    /**
     * setEventHandler
     * @phpcs:ignore
     * @param TreeAbstractEventHandlerInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $handler
     */
    public function setEventHandler(TreeAbstractEventHandlerInterface $handler): void
    {
        $this->eventHandler = $handler;
    }

    /**
     * getEventHandler
     * @return TreeAbstractEventHandlerInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>
     */
    public function getEventHandler(): TreeAbstractEventHandlerInterface
    {
        return $this->eventHandler;
    }

    /**
     * rootTest
     * encapsulate logic for testing whether something is or can be the root
     * @phpcs:ignore
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|TreenodeValueObjectInterface<PayloadType, ValueObjectType> $nodeItem
     * @return bool
     */
    public function rootTest(TreenodeAbstractInterface|TreenodeValueObjectInterface $nodeItem): bool
    {
        return (is_null($nodeItem->getParentId()));
    }

    /**
     * @function getRoot
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
     */
    public function getRoot(): TreenodeAbstractInterface|null
    {
        return $this->root ?? null;
    }

    /**
     * @function setRoot sets a reference to the root node of the tree
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     * @throws AlreadySetRootException
     */
    protected function setRoot($node): void
    {
        /**
         * if the root is already set, throw an exception
         */
        if (isset($this->root)) {
            throw new AlreadySetRootException();
        }
        $this->root = $node;
    }

    /**
     * isEmpty tells you whether the tree has any nodes or not.
     *
     * @function isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getNodes());
    }

    /**
     * initialize
     * initializes the tree, e.g. removes all the nodes and sets the root to null.
     */
    public function initialize(): void
    {
        $this->nodes = [];
        $this->root = null;
    }

    /**
     * @function getNodes
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @function getNode
     * @param non-negative-int|null $nodeId
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType>|null
     */
    public function getNode(?int $nodeId): ?TreenodeAbstractInterface
    {
        return $this->nodes[$nodeId] ?? null;
    }

    /**
     * @function nodeCount
     * @return int
     */
    public function nodeCount(): int
    {
        return count($this->getNodes());
    }

    /**
     * addNode
     * @param TreenodeValueObjectInterface<PayloadType, ValueObjectType> $valueObject
     */
    public function addNode(TreenodeValueObjectInterface $valueObject): void
    {
        $node = $this->treenodeFactory->makeNode();
        $node->hydrate($valueObject, $this);

        $this->eventHandler->beforeAddNode($node);

        $this->nodes[$node->getNodeId()] = $node;

        if ($this->rootTest($node)) {
            $this->setRoot($node);
        }

        $this->eventHandler->afterAddNode($node);
    }

    /**
     * hydrate
     * @param array<TreenodeValueObjectInterface<PayloadType, ValueObjectType>> $valueObjects
     */
    public function hydrate(array $valueObjects): void
    {
        /**
         * Check for this because the insertNodeRecurse method assumes a non-empty array to
         * work on.  If empty, just return - nothing to do.
         */
        if (empty($valueObjects)) {
            return;
        }

        /**
         * cannot hydrate a tree which is not empty
         */
        if (!$this->isEmpty()) {
            throw new TreeNotEmptyHydrationException();
        }

        /**
         * if the root is not set, find the (hopefully first and only) root node and set it
         */
        foreach ($valueObjects as $key => $nodeValueObject) {
            if ($this->rootTest($nodeValueObject)) {
                $rootNodeKey = $key;
                /**
                 * break the loop if we found the root
                 */
                break;
            }
        }
        if (!isset($rootNodeKey)) {
            throw new NoRootFoundException();
        } else {
            $this->insertNodeRecurse($rootNodeKey, $valueObjects);
        }
    }

    /**
     * insertNodeRecurse recursively inserts nodes into the tree using a depth first algorithm
     * @param int $startNodeKey
     * @param array<TreenodeValueObjectInterface<PayloadType, ValueObjectType>> $valueObjects
     * @return void
     */
    protected function insertNodeRecurse(int $startNodeKey, array $valueObjects): void
    {
        $valueObject = $valueObjects[$startNodeKey];

        $this->addNode($valueObject);

        $childValueObjects = [];
        foreach ($valueObjects as $key => $nodeValueObject) {
            /**
             * need identity, not equals, because 0 == null.  For example, if the root node has nodeId of 0, then it
             * gets inserted into the tree properly the first time.  When searching the array for children whose
             * parentId equals 0, the nodeValueObject for the root returns a parentId of null and if 0 == null, then
             * we try to add the root a second time as a child of itself.....
             */
            if ($valueObject->getNodeId() === $nodeValueObject->getParentId()) {
                $childValueObjects[$key] = $nodeValueObject;
            }
        }

        /**
         * sort the children (in place sort) - necessary only for ordered trees / nodes.  The implementation in the
         * unordered tree does nothing.  In the case or ordered trees / nodes, this step is critical because
         * you must add the nodes in order.  Adding nodes with indices of, say, [1, 5, 3, 4, 2] in the order
         * presented would result in scrambled nodes.  Indices of nodes get adjusted on their way into the tree if
         * the index is larger than the number of siblings of node already in the tree.
         */
        $this->sortChildValueObjects($childValueObjects);

        /**
         * recurse down through the children to hydrate the tree
         */
        foreach ($childValueObjects as $key => $nodeValueObject) {
            $this->insertNodeRecurse($key, $valueObjects);
        }
    }

    /**
     * sortChildValueObjects
     * @param array<TreenodeValueObjectInterface<PayloadType, ValueObjectType>> $childValueObjects
     * @return bool
     */
    abstract protected function sortChildValueObjects(array &$childValueObjects): bool;

    /**
     * @function deleteNode deletes a node from the tree.
     *
     * If deleteBranchOK is true then node and all its descendants will be deleted as well.  If deleteBranchOK is false
     * and $node is an interior node, throw an exception.
     *
     * @param non-negative-int $nodeId
     * @param bool $deleteBranchOK
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function deleteNode($nodeId, bool $deleteBranchOK = false): void
    {
        /**
         * if the node is not in the tree, throw an exception
         */
        if (!$node = $this->getNode($nodeId)) {
            throw new NodeNotInTreeException($this->getTreeId(), $nodeId);
        }

        /**
         * if this is an interior node and deleteBranchOK parameter is false, throw an exception
         */
        if (!$deleteBranchOK && $node->hasChildren()) {
            throw new DeleteInteriorNodeException($nodeId);
        }

        /**
         * if deleteBranchOK is true then recursively delete all descendants.  Finish by deleting the node.
         */
        $this->deleteNodeRecurse($node, $deleteBranchOK);

        /**
         * If this node happens to be the root of the tree, delete the root reference.
         */
        if ($node === $this->getRoot()) {
            unset($this->root);
        }
    }

    /**
     * @function deleteNodeRecurse does the actual work of deleting the node / branch
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, ValueObjectType> $node
     * @param bool $deleteBranch
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    protected function deleteNodeRecurse(TreenodeAbstractInterface $node, bool $deleteBranch): void
    {
        /**
         * if deleteBranchOK is true, delete all the children first.
         */
        if ($deleteBranch) {
            foreach ($node->getChildren() as $child) {
                /** @var NodeType $child */
                $this->deleteNodeRecurse($child, true);
            }
        }

        $this->eventHandler->beforeDeleteNode($node);

        /**
         * remove the node from the node list
         */
        unset($this->nodes[$node->getNodeId()]);

        $this->eventHandler->afterDeleteNode($node);
    }
}
