<?php declare(strict_types = 1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\err\_ExceptionFactory;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeArrayException;
use pvc\struct\tree\err\InvalidNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeHasInvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\RootCountForTreeException;
use pvc\struct\tree\err\SetNodesException;

/**
 * Class TreeOrdered
 *
 * by convention, root node of a tree has null as a parent (see treenode object).
 *
 * @template NodeValueType
 * @implements TreeOrderedInterface<NodeValueType>
 */
class TreeOrdered implements TreeOrderedInterface
{
	/** @phpstan-use TreeTrait<NodeValueType> */
	use TreeTrait;

	/**
	 * @var TreenodeOrderedInterface<NodeValueType>|null
	 */
	protected $root;

	/**
	 * @var TreenodeOrderedInterface<NodeValueType>[]
	 */
	protected array $nodes = [];

	/**
	 * @param int $treeId
	 * @throws \Exception
	 */
	public function __construct(int $treeId)
	{
		$this->setTreeId($treeId);
	}

	/**
	 * setRoot sets a reference to the root node of the tree
	 *
	 * @function setRoot
	 * @param TreenodeOrderedInterface<NodeValueType> $node
	 * @throws AlreadySetRootException
	 */
	protected function setRoot(TreenodeOrderedInterface $node): void
	{
		/**
		 * if the root is already set, throw an exception
		 */
		if (isset($this->root)) {
			throw _ExceptionFactory::createException(AlreadySetRootException::class);
		}

		$this->root = $node;
	}

	/**
	 * @function addNode
	 * @param TreenodeOrderedInterface<NodeValueType> $node
	 * @throws AlreadySetNodeidException
	 * @throws InvalidParentNodeException
	 * @throws InvalidTreeidException
	 */
	public function addNode(TreenodeOrderedInterface $node): void
	{
		/**
		 * node is added to the nodes property of the tree and if node is the root, the root gets set as well.
		 */
		$this->addNodeToNodelist($node);
		/**
		 * node sets the references to parent and tree objects
		 */
		$node->setReferences($this);
		/**
		 * add node to the child list of the parent.  Node has an index property which is used to position the node
		 * correctly in the list.  Need to coalesce getIndex to 0 because the static analyzers see that it can return
		 * null (but which cannot be true at this point in the code).
		 */
		if ($parent = $node->getParent()) {
			$parent->getChildren()->add($node->getIndex() ?? 0, $node);
		}
	}

	/**
	 * addNodeToNodelist
	 * @param TreenodeOrderedInterface<NodeValueType> $node
	 * @throws AlreadySetRootException
	 */
	protected function addNodeToNodelist($node) : void
	{
		/**
		 * make sure nodeid has been set
		 */
		if (is_null($nodeid = $node->getNodeId())) {
			throw _ExceptionFactory::createException(InvalidNodeIdException::class, [$nodeid]);
		}

		/**
		 * make sure nodeid does not already exist in the tree
		 */
		if (!is_null($this->getNode($nodeid))) {
			throw _ExceptionFactory::createException(AlreadySetNodeidException::class, [$nodeid]);
		}

		/**
		 * make sure the treeid of the node matches the tree's id.  Use a strict comparison so that in the off chance
		 * that the treeid is 0 and node's treeid is nul, we don't have a type casting problem.
		 */
		if ($this->getTreeId() !== $node->getTreeId()) {
			throw _ExceptionFactory::createException(NodeHasInvalidTreeidException::class, [
				$node->getNodeId(), $node->getTreeId(), $this->getTreeId()
			]);
		}

		/**
		 * if there's a parentid, make sure that exists in the tree as well
		 */
		$parentid = $node->getParentId();
		if ((!is_null($parentid)) && (is_null($this->getNode($parentid)))) {
			throw _ExceptionFactory::createException(InvalidParentNodeException::class, [$parentid]);
		}

		/**
		 * set node as the root if it has root characteristics
		 */
		if ($node->isRoot()) {
			$this->setRoot($node);
		}

		$this->nodes[$nodeid] = $node;
	}

	/**
	 * addNodesToNodelistAndSetRoot
	 * @param TreenodeOrderedInterface<NodeValueType>[] $nodeArray
	 * @param string $classString
	 * @throws AlreadySetRootException
	 * @throws CircularGraphException
	 */
	protected function addNodesToNodelistAndSetRoot(array $nodeArray, string $classString) : void
	{
		/**
		 * tree must be empty before calling this method.
		 */
		if (!$this->isEmpty()) {
			throw _ExceptionFactory::createException(SetNodesException::class);
		}

		/**
		 * if the array is empty, just return because the rest of the method starts testing actual node data
		 */
		if (empty($nodeArray)) {
			return;
		}

		/**
		 * make sure each node in the array has TreenodeInterface, belongs in this tree, and has a key that matches
		 * its nodeid
		 */
		foreach ($nodeArray as $key => $node) {
			if (!$node instanceof $classString) {
				throw _ExceptionFactory::createException(InvalidNodeException::class);
			}
			if ($node->getTreeId() != $this->getTreeId()) {
				throw _ExceptionFactory::createException(NodeHasInvalidTreeidException::class, [
					$node->getNodeId(), $node->getTreeId(), $this->getTreeId()
				]);
			}
			if ($key != $node->getNodeId()) {
				throw _ExceptionFactory::createException(InvalidNodeArrayException::class, [$key, $node->getNodeId()]);
			}
		}

		/**
		 * verify there is one root and that all non-null parent ids are in the array.
		 */
		$rootCount = 0;
		foreach ($nodeArray as $node) {
			if ($node->isRoot()) {
				$root = $node;
				$rootCount++;
			} else {
				$parentId = $node->getParentId();
				if (!isset($nodeArray[$parentId])) {
					throw _ExceptionFactory::createException(InvalidParentNodeException::class, [$parentId]);
				}
			}
		}
		if ($rootCount != 1) {
			throw _ExceptionFactory::createException(RootCountForTreeException::class, [$rootCount]);
		}

		/**
		 * insure there are no circular references in the tree and ensure array is keyed properly
		 */
		$keyedNodeArray = [];
		foreach ($nodeArray as $node) {
			$this->checkCircularity($node, $nodeArray);
			$keyedNodeArray[$node->getNodeId()] = $node;
		}

		/**
		 * add nodes and set the root.
		 * static analysis tools cannot see that $root must be set at this point.  The if statement is superfluous
		 * but makes the static analysis warning go away
		 */
		if (isset($root)) {
			$this->setRoot($root);
		}
		$this->nodes = $keyedNodeArray;
	}

	/**
	 * deletes a node from the tree.
	 *
	 * If deleteBranch is true then node and all its descendants will be deleted as well.  If deleteBranch is false
	 * and $nose is an interior node, then it throws an exception.
	 *
	 * @function deleteNode
	 * @param TreenodeOrderedInterface<NodeValueType> $node
	 * @param bool $deleteBranch
	 * @throws DeleteInteriorNodeException
	 * @throws NodeNotInTreeException
	 */
	public function deleteNode(TreenodeOrderedInterface $node, bool $deleteBranch = false) : void
	{
		/**
		 * make sure node is in the tree, and we are not trying to delete an interior node unless $deleteBranch is true.
		 */
		$this->verifyDeleteNodeInitialConditions($node, $deleteBranch);

		/**
		 * if deleteBranch is true then recursively delete all descendants.  Finish by deleting the node.
		 */
		$this->deleteNodeRecurse($node, $deleteBranch);

		/**
		 * If this node happens to be the root of the tree, delete the root reference.
		 */
		if ($node === $this->getRoot()) {
			$this->root = null;
		}

		/**
		 * delete reference to node in the parent's child list.  If this node happens to be the root, getParent
		 * returns null so there are no children to delete.  Also, throwing in a test that getIndex returns an int
		 * and not null because otherwise the static analyzers complain....
		 */
		if (($parent = $node->getParent()) && (!is_null($node->getIndex()))) {
			$parent->getChildren()->delete($node->getIndex());
		}
	}

	/**
	 * @function deleteNodeRecurse
	 * @param TreenodeOrderedInterface<NodeValueType> $node
	 * @param bool $deleteBranch
	 * @throws DeleteInteriorNodeException
	 * @throws NodeNotInTreeException
	 */
	protected function deleteNodeRecurse(TreenodeOrderedInterface $node, bool $deleteBranch): void
	{
		/**
		 * if deleteBranch is true and there are children, delete all the children first. Node keeps its own list of
		 * children.
		 */
		if ($deleteBranch && ($children = $node->getChildrenArray())) {
			foreach ($children as $child) {
				$this->deleteNodeRecurse($child, $deleteBranch);
			}
		}
		/**
		 * remove the node from the nodelist
		 */
		unset($this->nodes[$node->getNodeId()]);
	}

	/**
	 * @function setNodes
	 * @param TreenodeOrderedInterface<NodeValueType>[] $nodeArray
	 * @throws AlreadySetNodeidException
	 * @throws InvalidParentNodeException
	 * @throws InvalidTreeidException
	 */
	public function setNodes(array $nodeArray): void
	{
		/**
		 * add all the nodes to the array and set the root
		 */
		$this->addNodesToNodelistAndSetRoot($nodeArray, TreenodeOrderedInterface::class);

		/**
		 * set up all the references to actual objects in each node
		 */
		foreach ($this->nodes as $node) {
			$node->setReferences($this);
		}

		/**
		 * Get all the children of each node and populate the child list in the correct order.  This is a pretty ugly
		 * N-squared algorithm.  It could be improved by storing all the child nodeids in the same array as the ids
		 * for the node itself.  For example, if the root node had 3 children with ids 2, 3, and 7, the array storing
		 * the root's information could look like (0, null, 1, 2, 3, 7).  In other words, the first 3 elements are
		 * nodeid, parentid, and treeid.  All remaining elements would be children and would be in the order in which
		 * they are to be inserted into the child list.  That would make the data store logic more complicated
		 * because the arrays for the nodes would have different lengths but this logic to insert children gets easier
		 * and much faster.
		 */
		$callback = function (TreenodeOrderedInterface $node1, TreenodeOrderedInterface $node2) {
			/**
			 * spaceship operator returns -1 if node1 < node2, 0 if they are equal, +1 if Node1 > node2
			 */
				return ($node1->getIndex() <=> $node2->getIndex());
		};

		$i = 0;
		foreach($this->nodes as $parent) {
			$children = [];
			foreach($this->nodes as $child) {
				/**
				 * once again, use a strict equals to distinguish between 0 and null
				 */
				if ($child->getParentId() === $parent->getNodeId()) {
					$children[] = $child;
				}
			}
			/**
			 * usort is an "in place" sort, e.g. sorts the argument and does not return any value.  It destroys any
			 * original keys and creates new keys starting at 0.  This is perfect for inserting into an ordered list.
			 */
			usort($children, $callback);
			foreach($children as $index => $listElement) {
				$parent->getChildren()->add($index, $listElement);
				$i++;
			}
		}
	}

	/**
	 * @function getNodes
	 * @return TreenodeOrderedInterface<NodeValueType>[]
	 */
	public function getNodes(): array
	{
		return $this->nodes;
	}

	/**
     * @function getRoot
     * @return TreenodeOrderedInterface<NodeValueType>|null
     */
    public function getRoot(): ?TreenodeOrderedInterface
    {
        return $this->root;
    }

    /**
     * @function getNode
     * @param int $nodeid
     * @return TreenodeOrderedInterface<NodeValueType>|null
     */
    public function getNode(int $nodeid): ?TreenodeOrderedInterface
    {
        return $this->nodes[$nodeid] ?? null;
    }

    /**
     * @function getChildrenOf
     * @param TreenodeOrderedInterface<NodeValueType> $parent
     * @return TreenodeOrderedInterface<NodeValueType>[]
     * @throws NodeNotInTreeException
     */
    public function getChildrenOf(TreenodeOrderedInterface $parent): array
    {
        if (!$this->hasNode($parent)) {
            throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(),
                                                     $parent->getParentId()]);
        }
        return $parent->getChildren()->getElements();
    }

    /**
     * @function getParentOf
     * @param TreenodeOrderedInterface<NodeValueType> $node
     * @return TreenodeOrderedInterface<NodeValueType>|null
     * @throws NodeNotInTreeException
     */
    public function getParentOf(TreenodeOrderedInterface $node): ?TreenodeOrderedInterface
    {
        if (!$this->hasNode($node)) {
            throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(),
	            $node->getNodeId()]);
        }
        return $node->getParent();
    }

	/**
	 * getTreeDepthFirstRecurse does the actual work of traversing the tree.
	 *
	 * @function getTreeDepthFirstRecurse
	 * @param TreenodeOrderedInterface<NodeValueType> $startNode
	 * @param callable $callable
	 * @return TreenodeOrderedInterface<NodeValueType>[]
	 * @throws NodeNotInTreeException
	 */
	protected function getTreeDepthFirstRecurse($startNode, callable $callable): array
	{
		$result = [];

		/**
		 * if the filter callback returns true, add the node to the resultset.
		 */
		if ($callable($startNode)) {
			$result[$startNode->getNodeId()] = $startNode;
		}

		/**
		 * get the list of children and recurse on each child, merging the resultset arrays back together and
		 * returning the merged resultset.
		 */
		$children = $this->getChildrenOf($startNode);
		foreach ($children as $child) {
			$result = array_merge($result, $this->getTreeDepthFirstRecurse($child, $callable));
		}
		return $result;
	}

}
