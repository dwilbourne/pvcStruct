<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
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
 * @template CollectionType of CollectionInterface
 * @template TreeType of TreeInterface
 * @implements TreeInterface<TreenodeType, CollectionType>
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
     * @var array<TreenodeType>
     */
    protected array $nodes = [];

    /**
     * @var callable|null
     */
    protected $treenodeComparator = null;

    /**
     * @param  TreenodeFactoryInterface<TreenodeType, CollectionType>  $treenodeFactory
     */
    public function __construct(
        protected TreenodeFactoryInterface $treenodeFactory
    ) {
        $this->isInitialized = false;
    }

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * initialize
     * initializes the tree, e.g. removes all the nodes, sets the root to null, sets the treeId and
     * initializes the TreenodeFactory
     *
     * @param  non-negative-int  $treeId
     */
    public function initialize(int $treeId): void
    {
        $this->nodes = [];
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
    public function validateTreeId(int $nodeid): bool
    {
        return 0 <= $nodeid;
    }

    /**
     * @return CollectionInterface<TreenodeType>
     */
    public function makeCollection(): CollectionInterface
    {
        return $this->treenodeFactory->makeCollection();
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
     * @function getNodes
     * @return array<TreenodeType>
     */
    public function getNodes(): array
    {
        return $this->nodes;
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
     *
     * @param  TreenodeType|TreenodeDtoInterface  $nodeDto
     */
    public function addNode(TreenodeInterface|TreenodeDtoInterface $nodeDto
    ): void {
        if ($nodeDto instanceof TreenodeDtoInterface) {
            $node = $this->treenodeFactory->makeNode();
            $node->hydrate($nodeDto);
        } else {
            $node = $nodeDto;
        }

        /**
         * node id cannot already exist in the tree
         */
        if ($this->getNode($node->getNodeId()) !== null) {
            throw new AlreadySetNodeidException($node->getNodeId());
        }

        /**
         * set the tree reference.  Dto potentially has a null treeId
         */
        $node->setTree($this);

        /**
         * set the parent reference.  This has to happen after the tree is set
         * because the node will use the tree reference to get the parent
         * referenced by the node's parentId property
         */
        $node->setParent($node->getParentId());

        $this->nodes[$node->getNodeId()] = $node;

        if ($this->rootTest($node)) {
            $this->setRoot($node);
        }
    }

    /**
     * hydrate
     *
     * @param  array<TreenodeType|TreenodeDtoInterface>  $array
     */
    public function hydrate(array $array): void
    {
        if (!$this->isInitialized) {
            throw new TreeNotInitializedException();
        }

        /**
         * If empty, just return - nothing to do.  Otherwise, find the root
         */
        if (empty($array)) {
            return;
        }
        /** @var callable $callable */
        $callable = [$this, 'rootTest'];
        if (!$root = array_find($array, $callable)) {
            throw new NoRootFoundException();
        }

        /**
         * root found, insert nodes recursively
         * phpstan cannot know that this is a
         */
        $startNodeId = $root->getNodeId();
        $this->insertNodeRecurse($startNodeId, $array);
    }

    /**
     * insertNodeRecurse recursively inserts nodes into the tree using a depth first algorithm
     *
     * @param  int  $startNodeKey
     * @param  array<TreenodeType|TreenodeDtoInterface>  $array
     *
     * @return void
     */
    protected function insertNodeRecurse(int $startNodeKey, array $array): void
    {
        /** @var TreenodeType|TreenodeDtoInterface $start */
        $start = $array[$startNodeKey];
        $this->addNode($start);

        /**
         * use a collection here instead of an array so that the children are added to the node's child collection
         * in the proper order in case of an indexed collection
         */

        /**
         * filter dto array for children of $node
         *
         * need identity, not equals, because 0 == null.  For example, if the root node has nodeId of 0, then it
         * gets inserted into the tree properly the first time.  When searching the array for children whose
         * parentId equals 0, the nodeDto for the root returns a parentId of null and if 0 == null, then
         * we try to add the root a second time as a child of itself.....
         */

        $parentId = $start->getNodeId();
        $filter = function (TreenodeInterface|TreenodeDtoInterface $nodeDto) use
        (
            $parentId
        ): bool {
            return $parentId === $nodeDto->getParentId();
        };
        $children = array_filter($array, $filter);

        /**
         * if necessary, sort the dtos so they go into the tree in the correct order
         */
        if ($this->treenodeComparator) {
            uasort($children, $this->treenodeComparator);
        }

        /**
         * recurse down through the children to hydrate the tree.  The foreach looks a little odd because
         * we only need the key from the array, not the dto. The second parameter is the complete set of dtos we
         * are importing into the tree
         */
        foreach ($children as $key => $dto) {
            $this->insertNodeRecurse($key, $array);
        }
    }

    /**
     * @function getNode
     *
     * @param  non-negative-int|null  $nodeId
     *
     * @return TreenodeType|null
     */
    public function getNode(?int $nodeId): ?TreenodeInterface
    {
        return $this->nodes[$nodeId] ?? null;
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
        return is_null($nodeItem->getParentId());
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
            throw new NodeNotInTreeException($this->getTreeId(), $nodeId);
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
     * @function getTreeId
     * @return non-negative-int
     */
    public function getTreeId(): int
    {
        return $this->treeId;
    }

    /**
     * @param  non-negative-int  $treeId
     *
     * @return void
     * @throws InvalidTreeidException
     */
    protected function setTreeId(int $treeId): void
    {
        /**
         * because each node has a treeId property, TreenodeFactory throws an exception if the treeId is not
         * set when it tries to make a node.  This ensures that you cannot make a node without a treeId.
         */

        if (!$this->validateTreeId($treeId)) {
            throw new InvalidTreeIdException($treeId);
        }

        $this->treeId = $treeId;
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
        unset($this->nodes[$node->getNodeId()]);
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
}
