<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\err\_ExceptionFactory;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeArrayException;
use pvc\struct\tree\err\InvalidNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\NodeHasInvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\RootCountForTreeException;
use pvc\struct\tree\err\SetNodesException;

/**
 * Class Tree
 *
 * by convention, root node of a tree has null as a parent (see treenode object).
 *
 * @template NodeValueType
 * @implements TreeInterface<NodeValueType>
 */
class Tree implements TreeInterface
{
	/**
	 * @phpstan-use TreeTrait<NodeValueType>
	 */
	use TreeTrait;

	/**
	 * @var TreenodeInterface<NodeValueType>|null
	 */
	protected $root;

	/**
	 * @var TreenodeInterface<NodeValueType>[]
	 */
	protected array $nodes = [];

	public function __construct(int $treeid)
	{
		$this->setTreeId($treeid);
	}

	/**
	 * setRoot sets a reference to the root node of the tree
	 *
	 * @function setRoot
	 * @param TreenodeInterface<NodeValueType> $node
	 * @throws AlreadySetRootException
	 */
	protected function setRoot(TreenodeInterface $node): void
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
	 * addNode adds a single node into the tree.
	 *
	 * @param TreenodeInterface<NodeValueType> $node
	 * @throws \pvc\struct\tree\err\AlreadySetRootException
	 */
	public function addNode(TreenodeInterface $node): void
	{
		/**
		 * This seems like a waste, except that the add method for TreeOrdered also uses addNodeToNodelist and then
		 * does some more stuff.  So the common code between the two classes is shared in the addNodeToNodelist method
		 * kept in TreeTrait
		 */
		$this->addNodeToNodelist($node);
	}

	/**
	 * addNodeToNodelist
	 * @param TreenodeInterface<NodeValueType> $node
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
	 * @param TreenodeInterface<NodeValueType>[] $nodeArray
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
	 * @param TreenodeInterface<NodeValueType> $node
	 * @param bool $deleteBranch
	 * @throws DeleteInteriorNodeException
	 * @throws NodeNotInTreeException
	 */
	public function deleteNode(TreenodeInterface $node, bool $deleteBranch = false) : void
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
	}

	/**
	 * @function deleteNodeRecurse
	 * @param TreenodeInterface<NodeValueType> $node
	 * @param bool $deleteBranch
	 * @throws DeleteInteriorNodeException
	 * @throws NodeNotInTreeException
	 */
	protected function deleteNodeRecurse(TreenodeInterface $node, bool $deleteBranch): void
	{
		/**
		 * if deleteBranch is true, delete all the children first.
		 */
		if ($deleteBranch) {
			/**
			 * unlike TreeOrder where the node can get its own children, in this implementation we have to go to the
			 * tree and run through all the nodes to get the children.
			 */
			$children = $this->getChildrenOf($node);
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
	 * @function getRoot
	 * @return TreenodeInterface<NodeValueType>|null
	 */
	public function getRoot() : ? TreenodeInterface
	{
		return $this->root ?? null;
	}

	/**
	 * @function getNode
	 * @param int $nodeid
	 * @return TreenodeInterface<NodeValueType>|null
	 */
	public function getNode(int $nodeid) : ? TreenodeInterface
	{
		return $this->nodes[$nodeid] ?? null;
	}

	/**
     * setNodes populates (hydrates) the tree, insuring that the resulting tree is valid.
     *
     * The typical steps for populating the tree would be to pull "node data" from a data store and (probably using a
     * factory) create an array of nodes.  Supply that array of nodes to this method and then the tree will be
     * populated and valid.
     *
     * @param TreenodeInterface<NodeValueType>[] $nodeArray.  $nodeArray must have the form $nodeid => $node for each element.
     * The $nodeid key is used to insure all nodeid's are unique and to (easily) verify that each non-null parentid
     * has a corresponding node in the tree.  This method is used to hydrate the tree in one step.  Thus, the tree must
     * have no nodes in it in order to use this method successfully.
     */
    public function setNodes(array $nodeArray): void
    {
	    /**
	     * This seems like a waste, except that the add method for TreeOrdered also uses addNodesToNodelistAndSetRoot and then
	     * does some more stuff so the common code between the two classes is shared in the addNodeToNodelist method
	     * kept in TreeTrait
	     */
		$this->addNodesToNodelistAndSetRoot($nodeArray, TreenodeInterface::class);
    }

	/**
	 * @function getNodes
	 * @return TreenodeInterface<NodeValueType>[]
	 */
	public function getNodes(): array
	{
		return $this->nodes;
	}

	/**
	 * @function getChildrenOf
	 * @param TreenodeInterface<NodeValueType> $parent
	 * @return TreenodeInterface<NodeValueType>[]
	 * @throws \Exception
	 */
    public function getChildrenOf(TreenodeInterface $parent): array
    {
	    /**
	     * throw an exception if parent is not in the tree
	     */
	    if (!$this->hasNode($parent)) {
		    throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(),
			    $parent->getNodeId()]);
	    }

        $parentId = $parent->getNodeId();

	    /**
	     * criteria for success is that the node's parentid equals the one calculated above.  Use strict === because
	     * getParentId returning null will be cast to 0 with a "==" equality test
	     */
        $filter = function (TreenodeInterface $node) use ($parentId) {
            return $parentId === $node->getParentId();
        };

        return array_filter($this->getNodes(), $filter);
    }

    /**
     * @function getParentOf
     * @param TreenodeInterface<NodeValueType> $node
     * @return TreenodeInterface<NodeValueType>|null
     * @throws NodeNotInTreeException
     */
    public function getParentOf(TreenodeInterface $node): ?TreenodeInterface
    {
        if (!$this->hasNode($node)) {
            throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(),
	            $node->getNodeId
	        ()]);
        }
        return $this->nodes[$node->getParentId()];
    }

	/**
	 * getTreeDepthFirstRecurse does the actual work of traversing the tree.
	 *
	 * @function getTreeDepthFirstRecurse
	 * @param TreenodeInterface<NodeValueType> $startNode
	 * @param callable $callable
	 * @return TreenodeInterface<NodeValueType>[]
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
