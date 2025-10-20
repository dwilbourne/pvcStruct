<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\err\NonExistentKeyException;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\TreeNotInitializedException;

/**
 * @class Tree
 * @template TreeId of array-key
 * @template NodeId of array-key
 * @template NodeType of TreenodeInterface
 * @implements TreeInterface<TreeId, NodeId, NodeType>
 */
class Tree implements TreeInterface
{
    /**
     * @var bool
     */
    protected bool $isInitialized;

    /**
     * @var TreeId
     */
    protected $treeId;

    /**
     * @var NodeType|null
     */
    protected TreenodeInterface|null $root = null;

    /**
     * @var TreenodeCollectionInterface<NodeId, NodeType>
     */
    protected TreenodeCollectionInterface $collection;

    /**
     * @param ValTesterInterface<mixed> $treeIdTester
     * @param  TreenodeFactoryInterface<NodeType>  $treenodeFactory
     *
     * In this class, the collection factory only needs to make something that has TreenodeCollectionInterface.
     * It is not necessary to give it the flexibility of creating multiple types (e.g. we do not need a
     * CollectionType template in this case).
     *
     * @param TreenodeCollectionFactoryInterface<TreenodeCollectionInterface<NodeId, NodeType>> $collectionFactory
     */
    public function __construct(
        protected ValTesterInterface $treeIdTester,
        protected TreenodeFactoryInterface $treenodeFactory,
        protected TreenodeCollectionFactoryInterface $collectionFactory,
    ) {
        $this->isInitialized = false;
    }

    /**
     * initialize
     * initializes the tree, e.g. removes all the nodes, sets the root to null, sets the treeId
     *
     * @param  TreeId  $treeId
     */
    public function initialize($treeId): void
    {
        $this->collection = $this->collectionFactory->makeCollection();
        $this->root = null;
        $this->setTreeId($treeId);
        /**
         * at this point the tree is in a valid state and is therefore initialized, even if it does not have
         * nodes yet
         */
        $this->isInitialized = true;
    }

    /**
     * @param  TreeId  $treeId
     *
     * @return void
     * @throws InvalidTreeidException
     */
    protected function setTreeId($treeId): void
    {
        if (!$this->treeIdTester->testValue($treeId)) {
            throw new InvalidTreeidException();
        }
        $this->treeId = $treeId;
    }

    /**
     * @function setRoot sets a reference to the root node of the tree
     *
     * @param  NodeType  $node
     *
     * @throws AlreadySetRootException
     */
    protected function setRoot(TreenodeInterface $node): void
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
     * @function getRoot
     * @return NodeType|null
     * leave the return type unspecified because when we extend this class we
     * want to be able to get a covariant return type
     */
    public function getRoot()
    {
        return $this->root ?? null;
    }

    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }


    /**
     * addNode
     *
     * @param  NodeType  $node
     * @param  NodeType|null  $parent
     */
    public function addNode($node, $parent): void
    {
        if (!$this->isInitialized) {
            throw new TreeNotInitializedException();
        }

        /**
         * node cannot already exist in the tree
         */

        /** @var NodeId $nodeId */
        $nodeId = $node->getNodeId();

        if ($this->getNode($nodeId) !== null) {
            throw new AlreadySetNodeidException($nodeId);
        }

        $node->setTree($this);
        $node->setParent($parent);

        /**
         * if it is the root, set the property
         */
        if ($this->rootTest($node)) {
            $this->setRoot($node);
        }

        /**
         * add the node to the node collection
         */
        $this->collection->add($node, $nodeId);
    }

    /**
     * @function deleteNode deletes a node from the tree.
     *
     * If deleteBranchOK is true then node and all its descendants will be deleted as well.  If deleteBranchOK is false
     * and $node is an interior node, throw an exception.
     *
     * @param  NodeId  $nodeId
     * @param  bool  $deleteBranchOK
     *
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    public function deleteNode($nodeId, bool $deleteBranchOK = false): void
    {
        /**
         * if the node is not in the tree, throw an exception
         */
        if (!$node = $this->getNode($nodeId)) {
            throw new NodeNotInTreeException($this->treeId, $nodeId);
        }

        /**
         * if this is an interior node and deleteBranchOK parameter is false, throw an exception
         */
        if (!$deleteBranchOK && $node->hasChildren()) {
            throw new DeleteInteriorNodeException($nodeId);
        }

        /**
         * delete children first if $deleteBranchOk is true and then delete this node
         */
        $this->deleteNodeRecurse($node, $deleteBranchOK);

        /**
         * If this node happens to be the root of the tree, delete the root reference.
         */
        if ($node === $this->getRoot()) {
            $this->root = null;
        }
    }

    /**
     * hydrate
     *
     * @param  array<TreenodeDtoInterface<NodeId, TreeId>>  $array
     */
    public function hydrate(array $array): void
    {
        if (!$this->isInitialized) {
            throw new TreeNotInitializedException();
        }

        /**
         * If empty, just return - nothing to do.  Otherwise, find the root
         * and start the recursion
         */
        if (!empty($array)) {
            $root = array_find($array, [$this, 'rootTest']);
            if ($root === null) {
                throw new NoRootFoundException();
            } else {
                $this->insertNodeRecurse($root->getNodeId(), $array);
            }
        }
    }


    /**
     * insertNodeRecurse recursively inserts nodes into the tree using a depth first algorithm
     *
     * @param  NodeId  $nodeId
     * @param  array<TreenodeDtoInterface<NodeId, TreeId>>  $array
     *
     * @return void
     */
    protected function insertNodeRecurse($nodeId, array $array): void
    {
        $dto = $array[$nodeId];

        /**
         * make the node we are going to insert
         */
        $node = $this->treenodeFactory->makeNode();

        /**
         * set the nodeId
         */
        $node->setNodeId($nodeId);

        /**
         * if the dto has a non-null treeId, ensure it matches that of this tree
         */
        if (($dto->getTreeId() !== null) && ($this->treeId !== $dto->getTreeId())) {
            throw new InvalidTreeidException((string) $dto->getTreeId());
        } else {
            $node->setTree($this);
        }

        /**
         * get the parent from the tree
         * not sure what phpstan's problem is here....
         */

        $parentId = $dto->getParentId();
        /** @phpstan-ignore-next-line */
        $parent = is_null($parentId) ? null : $this->getNode($parentId);

        /**
         * set the index property
         */
        $node->setIndex($dto->getIndex());

        /**
         * add the node
         */
        $this->addNode($node, $parent);

        /**
         * filter dto array for children of $node
         *
         * need identity, not equals, because 0 == null.  For example, if the root node has nodeId of 0, then it
         * gets inserted into the tree properly the first time.  When searching the array for children whose
         * parentId equals 0, the nodeDto for the root returns a parentId of null and if 0 == null, then
         * we try to add the root a second time as a child of itself.....
         */
        $filter = function (TreenodeDtoInterface $dto) use ($nodeId): bool {
            return $nodeId === $dto->getParentId();
        };
        $children = array_filter($array, $filter);

        /**
         * children must be inserted in index order
         */
        $comparator = function (TreenodeDtoInterface $a, TreenodeDtoInterface $b): int {
            return $a->getIndex() <=> $b->getIndex();
        };
        uasort($children, $comparator);

        /**
         * recurse down through the children to hydrate the tree.  The foreach looks a little odd because
         * we only need the key from the array, not the dto. The second parameter is the complete set of dtos we
         * are importing into the tree
         *
         * @var NodeId $key
         */
        foreach ($children as $key => $childDto) {
            $this->insertNodeRecurse($key, $array);
        }
    }

    /**
     * @return array<TreenodeDtoInterface<NodeId, TreeId>>
     * @throws TreeNotInitializedException
     */
    public function dehydrate(): array
    {
        $result = [];
        /**
         * why in the world does this collection not iterate properly????
         * foreach ($this->collection as node) fails!!!!
         */
        /** @var NodeType $node */
        foreach ($this->collection->getElements() as $node) {
            /** @var NodeId $nodeId */
            $nodeId = $node->getNodeId();
            $parentId = $node->getParent()?->getNodeId();
            $treeId = $this->treeId;
            $index = $node->getIndex();
            $dto = new TreenodeDto($nodeId, $parentId, $treeId, $index);
            $result[$nodeId] = $dto;
        }
        return $result;
    }


    /**
     * @function getNodes
     * @return CollectionInterface<TreeId, NodeType>
     */
    public function getNodeCollection(): CollectionInterface
    {
        return $this->collection;
    }

    /**
     * @function getNode
     *
     * @param  NodeId  $nodeId
     *
     * @return NodeType|null
     */
    public function getNode($nodeId): ?TreenodeInterface
    {
        /**
         * Collection::getElement throws an exception if the key does not exist
         * but we do not want that here.
         */
        try {
            return $this->collection->getElement($nodeId);
        } catch (NonExistentKeyException) {
            return null;
        }
    }

    /**
     * rootTest
     * encapsulate logic for testing whether something is or can be the root
     *
     * @param  NodeType|TreenodeDtoInterface<NodeId, TreeId>  $root
     *
     * @return bool
     */
    public function rootTest($root): bool
    {
        if ($root instanceof TreenodeInterface) {
            $parent = $root->getParent();
        } else {
            $parent = $root->getParentId();
        }
        return is_null($parent);
    }

    /**
     * @function deleteNodeRecurse does the actual work of deleting the node / branch
     *
     * @param  NodeType  $node
     * @param bool $deleteBranchOk
     *
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    protected function deleteNodeRecurse(
        TreenodeInterface $node,
        bool $deleteBranchOk
    ): void {
        /**
         * delete all the children first.
         */
        if ($deleteBranchOk) {
            $children = $node->getChildren();
            /** @var NodeType $child */
            foreach ($children as $child) {
                $this->deleteNodeRecurse($child, true);
            }
        }

        /**
         * remove the node from the node list
         * for some reason phpstan needs the type hint
         */
        /** @var NodeId $nodeId */
        $nodeId = $node->getNodeId();
        $this->collection->delete($nodeId);
    }
}
