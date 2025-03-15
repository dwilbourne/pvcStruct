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
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\NoRootFoundException;
use pvc\struct\tree\err\TreeNotInitializedException;

/**
 * @class Tree
 * @template PayloadType
 * @implements TreeInterface<PayloadType>
 * @phpstan-import-type TreenodeDtoShape from TreenodeDtoInterface
 */
class Tree implements TreeInterface
{
    /**
     * @var bool
     */
    protected bool $isInitialized;

    /**
     * @var int
     */
    protected int $treeId;

    /**
     * @var TreenodeInterface<PayloadType>|null
     */
    protected TreenodeInterface|null $root;

    /**
     * @var array<TreenodeInterface<PayloadType>>
     */
    protected array $nodes = [];

    /**
     * @var callable|null
     */
    protected $treenodeDtoComparator = null;

    /**
     * @param TreenodeFactoryInterface<PayloadType> $treenodeFactory
     */
    public function __construct(protected TreenodeFactoryInterface $treenodeFactory)
    {
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
     * @param non-negative-int $treeId
     * @param array<TreenodeDtoShape&TreenodeDTOInterface<PayloadType>> $dtos
     */
    public function initialize(int $treeId, array $dtos = []): void
    {
        $this->nodes = [];
        $this->root = null;
        $this->setTreeId($treeId);
        $this->treenodeFactory->initialize($this);
        /**
         * at this point the tree is in a valid state and is therefore initialized, even if it does not have
         * nodes yet
         */
        $this->isInitialized = true;
        $this->hydrate($dtos);
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
        return $this->treeId;
    }

    /**
     * @param non-negative-int $treeId
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
     * @return TreenodeFactoryInterface<PayloadType>
     */
    public function getTreenodeFactory(): TreenodeFactoryInterface
    {
        if (!$this->isInitialized()) {
            throw new TreeNotInitializedException();
        }
        return $this->treenodeFactory;
    }

    /**
     * rootTest
     * encapsulate logic for testing whether something is or can be the root
     * @param TreenodeInterface<PayloadType>|(TreenodeDtoShape&TreenodeDtoInterface<PayloadType>) $nodeItem
     * @return bool
     */
    public function rootTest(TreenodeInterface|TreenodeDtoInterface $nodeItem): bool
    {
        if ($nodeItem instanceof TreenodeInterface) {
            return is_null($nodeItem->getParentId());
        } else {
            return is_null($nodeItem->parentId);
        }
    }

    /**
     * @function getRoot
     * @return TreenodeInterface<PayloadType>|null
     */
    public function getRoot(): TreenodeInterface|null
    {
        return $this->root ?? null;
    }

    /**
     * @function setRoot sets a reference to the root node of the tree
     * @param TreenodeInterface<PayloadType> $node
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
     * @function getNodes
     * @return array<TreenodeInterface<PayloadType>>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @function getNode
     * @param non-negative-int|null $nodeId
     * @return TreenodeInterface<PayloadType>|null
     */
    public function getNode(?int $nodeId): ?TreenodeInterface
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
     * @param TreenodeDtoShape&TreenodeDtoInterface<PayloadType> $dto
     */
    public function addNode(TreenodeDtoInterface $dto): void
    {
        $node = $this->treenodeFactory->makeNode();
        $node->hydrate($dto);

        $this->nodes[$node->getNodeId()] = $node;

        if ($this->rootTest($node)) {
            $this->setRoot($node);
        }
    }

    /**
     * hydrate
     * @param array<TreenodeDtoShape&TreenodeDTOInterface<PayloadType>> $dtos
     * this metyhod is protected and only called from within the initialize method, which has a required treeId
     * parameter.  That ensures that we can never hydrate the tree without the treeId being set.
     */
    protected function hydrate(array $dtos): void
    {
        /**
         * Check for this because the insertNodeRecurse method assumes a non-empty Collection to
         * work on.  If empty, just return - nothing to do.
         */
        if (empty($dtos)) {
            return;
        }

        /** @var callable $callable */
        $callable = [$this, 'rootTest'];
        if (!$root = array_find($dtos, $callable)) {
            throw new NoRootFoundException();
        } else {
            $startNodeId = $root->nodeId;
            $this->insertNodeRecurse($startNodeId, $dtos);
        }
    }

    /**
     * insertNodeRecurse recursively inserts nodes into the tree using a depth first algorithm
     * @param int $startNodeKey
     * @param array<TreenodeDtoShape&TreenodeDtoInterface<PayloadType>> $dtos
     * @return void
     */
    protected function insertNodeRecurse(int $startNodeKey, array $dtos): void
    {
        $dto = $dtos[$startNodeKey];
        $this->addNode($dto);
        $parentId = $dto->nodeId;

        /**
         * use a collection here instead of an array so that the children are added to the node's child collection
         * in the proper order in case of an indexed collection
         */

        /**
         * @param TreenodeDtoInterface<PayloadType> $dto
         * @return bool
         *
         * filter dto array for children of $node
         *
         * need identity, not equals, because 0 == null.  For example, if the root node has nodeId of 0, then it
         * gets inserted into the tree properly the first time.  When searching the array for children whose
         * parentId equals 0, the nodeDto for the root returns a parentId of null and if 0 == null, then
         * we try to add the root a second time as a child of itself.....
         */
        $filter = function (TreenodeDtoInterface $dto) use ($parentId): bool
            {
                /**
                 * @var TreenodeDtoShape&TreenodeDtoInterface<PayloadType> $dto
                 */
                return $parentId === $dto->parentId;
            };
        $childDtos = array_filter($dtos, $filter);

        /**
         * if necessary, sort the dtos so they go into the tree in the correct order
         */
        if ($this->treenodeDtoComparator) {
            uasort($childDtos, $this->treenodeDtoComparator);
        }

        /**
         * recurse down through the children to hydrate the tree.  The foreach looks a little odd because
         * we only need the key from the array, not the dto. The second parameter is the complete set of dtos we
         * are importing into the tree
         */
        foreach ($childDtos as $key => $dto) {
            $this->insertNodeRecurse($key, $dtos);
        }
    }

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
     * @function deleteNodeRecurse does the actual work of deleting the node / branch
     * @param TreenodeInterface<PayloadType> $node
     * @throws DeleteInteriorNodeException
     * @throws NodeNotInTreeException
     */
    protected function deleteNodeRecurse(TreenodeInterface $node, bool $deleteBranchOk): void
    {
        /**
         * delete all the children first.
         */
        if ($deleteBranchOk) {
            $children = $node->getChildren();
            foreach ($children as $child) {
                $this->deleteNodeRecurse($child, true);
            }
        }

        /**
         * remove the node from the node list
         */
        unset($this->nodes[$node->getNodeId()]);
    }
}
