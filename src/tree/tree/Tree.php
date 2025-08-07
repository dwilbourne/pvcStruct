<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\struct\tree\tree\TreenodeCollectionInterface;
use pvc\struct\collection\err\NonExistentKeyException;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\TreeNotInitializedException;

/**
 * @class Tree
 * @template TreenodeType of TreenodeInterface
 * @implements TreeInterface<TreenodeType>
 */
class Tree implements TreeInterface
{
    /**
     * @var bool
     */
    protected bool $isInitialized;

    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var TreenodeType|null
     */
    protected TreenodeInterface|null $root;

    /**
     * @param  TreenodeFactoryInterface<TreenodeType>  $treenodeFactory
     * @param TreenodeCollectionInterface<TreenodeType> $collection
     */
    public function __construct(
        protected TreenodeFactoryInterface $treenodeFactory,
        protected TreenodeCollectionInterface $collection,
    ) {
        $this->isInitialized = false;
    }

    /**
     * initialize
     * initializes the tree, e.g. removes all the nodes, sets the root to null, sets the treeId
     *
     * @param  non-negative-int  $treeId
     */
    public function initialize(int $treeId): void
    {
        $this->collection->initialize();
        $this->root = null;
        $this->setTreeId($treeId);
        /**
         * at this point the tree is in a valid state and is therefore initialized, even if it does not have
         * nodes yet
         */
        $this->isInitialized = true;
    }

    /**
     * validateTreeId
     *
     * all tree ids are integers >= 0
     *
     * @param  int  $nodeid
     *
     * @return bool
     */
    protected function validateTreeId(int $nodeid): bool
    {
        return 0 <= $nodeid;
    }

    /**
     * @param  non-negative-int  $treeId
     *
     * @return void
     * @throws InvalidTreeidException
     */
    protected function setTreeId(int $treeId): void
    {
        if (!$this->validateTreeId($treeId)) {
            throw new InvalidTreeIdException($treeId);
        }
        $this->treeId = $treeId;
    }

    /**
     * @function setRoot sets a reference to the root node of the tree
     *
     * @param  TreenodeType  $node
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
     * @return TreenodeType|null
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
     * @param  TreenodeType  $node
     * @param  TreenodeType  $parent
     */
    public function addNode(TreenodeInterface $node, ?TreenodeInterface $parent): void
    {
        /**
         * node cannot already exist in the tree
         */
        $nodeId = $node->getNodeId();
        if ($this->getNode($nodeId) !== null) {
            throw new AlreadySetNodeidException($node->getNodeId());
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
        $this->collection->add($node->getNodeId(), $node);
    }

    /**
     * @function deleteNode deletes a node from the tree.
     *
     * If deleteBranchOK is true then node and all its descendants will be deleted as well.  If deleteBranchOK is false
     * and $node is an interior node, throw an exception.
     *
     * @param  non-negative-int  $nodeId
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
     * @param  array<TreenodeDtoInterface>  $array
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
     * @param  int  $nodeId
     * @param  array<TreenodeDtoInterface>  $array
     *
     * @return void
     */
    protected function insertNodeRecurse(int $nodeId, array $array): void
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
            throw new InvalidTreeidException($dto->getTreeId());
        }

        /**
         * get the parent from the tree
         */
        $parentId = $dto->getParentId();
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
         */
        foreach ($children as $key => $childDto) {
            $this->insertNodeRecurse($key, $array);
        }
    }

    /**
     * @return array<TreenodeDtoInterface>
     * @throws TreeNotInitializedException
     */
    public function dehydrate(): array
    {
        $result = [];
        /**
         * why in the world does this collection not iterate properly????
         * foreach ($this->collection as node) fails!!!!
         */
        /** @var TreenodeType $node */
        foreach ($this->collection->getElements() as $node) {
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
     * @return TreenodeCollectionInterface<TreenodeType>
     */
    public function getNodeCollection(): TreenodeCollectionInterface
    {
        return $this->collection;
    }

    /**
     * @function getNode
     *
     * @param  non-negative-int  $nodeId
     *
     * @return TreenodeType|null
     */
    public function getNode(int $nodeId): ?TreenodeInterface
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
     * @param  TreenodeType|TreenodeDtoInterface  $nodeItem
     *
     * @return bool
     */
    public function rootTest($nodeItem): bool
    {
        if ($nodeItem instanceof TreenodeInterface) {
            $parent = $nodeItem->getParent();
        } else {
            $parent = $nodeItem->getParentId();
        }
        return is_null($parent);
    }

    /**
     * @function deleteNodeRecurse does the actual work of deleting the node / branch
     *
     * @param  TreenodeType  $node
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
            /** @var TreenodeType $child */
            foreach ($children as $child) {
                $this->deleteNodeRecurse($child, true);
            }
        }

        /**
         * remove the node from the node list
         */
        $this->collection->delete($node->getNodeId());
    }



}
