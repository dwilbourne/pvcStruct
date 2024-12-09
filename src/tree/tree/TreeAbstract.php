<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
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
 * @template DtoType of TreenodeDTOInterface
 * @template CollectionType of CollectionAbstractInterface
 * @template DtoType of TreenodeDTOInterface
 * @implements TreeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>
 */
abstract class TreeAbstract implements TreeAbstractInterface
{
    /**
     * @var int
     */
    protected int $treeid;

    /**
     * @var TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType> $treenodeFactory
     */
    protected TreenodeFactoryInterface $treenodeFactory;

    /**
     * @var TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>|null
     */
    protected $root;

    /**
     * @var array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>>
     */
    protected array $nodes = [];

    /**
     * @param int $treeid
     * @phpcs:ignore
     * @param TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType> $treenodeFactory
     * @phpcs:ignore
     * @throws InvalidTreeidException
     * @throws SetTreeIdException
     */
    public function __construct(
        int $treeid,
        TreenodeFactoryInterface $treenodeFactory,
    ) {
        $this->setTreeId($treeid);
        $this->setTreenodeFactory($treenodeFactory);
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
     * @return TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>
     */
    public function getTreenodeFactory(): TreenodeFactoryInterface
    {
        return $this->treenodeFactory;
    }

    /**
     * @phpcs:ignore
     * @param TreenodeFactoryInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType> $treenodeFactory
     */
    public function setTreenodeFactory(TreenodeFactoryInterface $treenodeFactory): void
    {
        $treenodeFactory->setTree($this);
        $this->treenodeFactory = $treenodeFactory;
    }

    /**
     * rootTest
     * encapsulate logic for testing whether something is or can be the root
     * @phpcs:ignore
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>|TreenodeDTOInterface<PayloadType, DtoType> $nodeItem
     * @return bool
     */
    public function rootTest(TreenodeAbstractInterface|TreenodeDTOInterface $nodeItem): bool
    {
        if ($nodeItem instanceof TreenodeAbstractInterface) {
            return is_null($nodeItem->getParentId());
        } else {
            return is_null($nodeItem->parentId);
        }
    }

    /**
     * @function getRoot
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>|null
     */
    public function getRoot(): TreenodeAbstractInterface|null
    {
        return $this->root ?? null;
    }

    /**
     * @function setRoot sets a reference to the root node of the tree
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType> $node
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
     * @function getNodeIds
     * @return array<TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @function getNode
     * @param non-negative-int|null $nodeId
     * @return TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>|null
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
     * @param TreenodeDTOInterface<PayloadType, DtoType> $dto
     */
    public function addNode(TreenodeDTOInterface $dto): void
    {
        $node = $this->treenodeFactory->makeNode();
        $node->hydrate($dto, $this);

        $this->nodes[$node->getNodeId()] = $node;

        if ($this->rootTest($node)) {
            $this->setRoot($node);
        }
    }

    /**
     * hydrate
     * @param array<TreenodeDTOInterface<PayloadType, DtoType>> $dtos
     */
    public function hydrate(array $dtos): void
    {
        /**
         * Check for this because the insertNodeRecurse method assumes a non-empty array to
         * work on.  If empty, just return - nothing to do.
         */
        if (empty($dtos)) {
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
        foreach ($dtos as $key => $nodeDto) {
            if ($this->rootTest($nodeDto)) {
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
            $this->insertNodeRecurse($rootNodeKey, $dtos);
        }
    }

    /**
     * insertNodeRecurse recursively inserts nodes into the tree using a depth first algorithm
     * @param int $startNodeKey
     * @param array<TreenodeDTOInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType>> $dtos
     * @return void
     */
    protected function insertNodeRecurse(int $startNodeKey, array $dtos): void
    {
        $dto = $dtos[$startNodeKey];

        $this->addNode($dto);

        $childDtos = [];
        foreach ($dtos as $key => $nodeDto) {
            /**
             * need identity, not equals, because 0 == null.  For example, if the root node has nodeId of 0, then it
             * gets inserted into the tree properly the first time.  When searching the array for children whose
             * parentId equals 0, the nodeDto for the root returns a parentId of null and if 0 == null, then
             * we try to add the root a second time as a child of itself.....
             */
            if ($dto->nodeId === $nodeDto->parentId) {
                $childDtos[$key] = $nodeDto;
            }
        }

        /**
         * sort the children (in place sort) - necessary only for ordered trees / nodes.  The implementation in the
         * unordered tree does nothing.  In the case or ordered trees / nodes, this step is critical because
         * you must add the nodes in order.  Adding nodes with indices of, say, [1, 5, 3, 4, 2] in the order
         * presented would result in scrambled nodes.  Indices of nodes get adjusted on their way into the tree if
         * the index is larger than the number of siblings of node already in the tree.
         */
        $this->sortChildDtos($childDtos);

        /**
         * recurse down through the children to hydrate the tree
         */
        foreach ($childDtos as $key => $nodeDto) {
            $this->insertNodeRecurse($key, $dtos);
        }
    }

    /**
     * sortChildDtos
     * @param array<TreenodeDTOInterface<PayloadType, DtoType>> $childDtos
     * @return bool
     */
    abstract protected function sortChildDtos(array &$childDtos): bool;

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
     * @param TreenodeAbstractInterface<PayloadType, NodeType, TreeType, CollectionType, DtoType> $node
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

        /**
         * remove the node from the node list
         */
        unset($this->nodes[$node->getNodeId()]);
    }
}
