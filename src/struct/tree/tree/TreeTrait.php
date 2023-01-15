<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\struct\tree\err\_ExceptionFactory;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\BadTreesearchLevelsException;
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
use pvc\struct\tree\err\SetTreeIdException;

/**
 * Trait TreeTrait
 * @template NodeValueType
 */
trait TreeTrait
{

	/**
	 * @var int
	 */
	protected int $treeid;

	/**
	 * all treeids are integers >= 0
	 *
	 * validateTreeId
	 * @param int $nodeid
	 * @return bool
	 */
	private function validateTreeId(int $nodeid): bool
	{
		return 0 <= $nodeid;
	}

	/**
	 * @function setTreeId
	 * @param int $id
	 */
	public function setTreeId(int $id): void
	{
		/**
		 * treeid must pass validation
		 */
		if (!$this->validateTreeId($id)) {
			throw _ExceptionFactory::createException(InvalidTreeidException::class, [$id]);
		}

		/**
		 * treeid can only be changed if the tree is empty
		 */
		if (!$this->isEmpty()) {
			throw _ExceptionFactory::createException(SetTreeIdException::class);
		}
		$this->treeid = $id;
	}

	/**
	 * @function getTreeId
	 * @return int|null
	 */
	public function getTreeId(): ? int
	{
		return $this->treeid ?? null;
	}

	/**
	 * checkCircularity verifies there are no circularities in the tree structure.
	 *
	 * @function checkCircularity
	 * @param TreenodeInterface<NodeValueType> $node
	 * @param TreenodeInterface<NodeValueType>[] $nodeArray
	 * @throws CircularGraphException
	 */
	protected function checkCircularity($node, array $nodeArray): void
	{
		$nodeid = $node->getNodeId();
		$ancestorid = $node->getParentId();

		/**
		 * if ancestor id is null then we have bubbled up to the root of the tree, so no circularities
		 */
		while ($ancestorid !== null) {
			if ($nodeid == $ancestorid) {
				throw _ExceptionFactory::createException(CircularGraphException::class, [$nodeid]);
			}
			/**
			 * move ancestorid up the tree one more level and repeat
			 */
			$ancestor = $nodeArray[$ancestorid];
			$ancestorid = $ancestor->getParentid();
		}
	}

	/**
	 * hasNode does an object compare between its argument and each node in the tree, returning true
	 * if it finds a match.  The $strict parameter controls whether the method uses "==" (all properties have the
	 * same values) or "===" ($obj1 and $obj2 are the same instance).
	 *
	 * @function hasNode
	 * @param TreenodeInterface<NodeValueType>|null $nodeToBeTested
	 * @param bool $strict
	 * @return bool
	 */
	public function hasNode($nodeToBeTested = null, bool $strict = true): bool
	{
		foreach($this->getNodes() as $node) {
			if ($node->equals($nodeToBeTested, $strict)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * isEmpty tells you whether the tree has any nodes or not.
	 *
	 * @function isEmpty
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return (0 == $this->nodeCount());
	}

	/**
	 * @function nodeCount
	 * @return int
	 */
	public function nodeCount(): int
	{
		return count($this->nodes);
	}


	/**
	 * @function verifyDeleteNodeInitialConditions
	 * @param TreenodeInterface<NodeValueType> $node
	 * @param bool $deleteBranch
	 * @throws \Exception
	 */
	protected function verifyDeleteNodeInitialConditions(TreenodeInterface $node, bool $deleteBranch) : void
	{
		$nodeid = $node->getNodeId();

		/**
		 * if the node is not in the tree, throw an exception
		 */
		if (!$this->hasNode($node)) {
			throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(), $nodeid]);
		}

		/**
		 * if this is an interior node and deleteBranch parameter is false, throw an exception
		 */
		if (!$deleteBranch && $this->hasInteriorNodeWithId($nodeid)) {
			throw _ExceptionFactory::createException(DeleteInteriorNodeException::class, [$nodeid]);
		}
	}

	/**
	 * getTreeDepthFirst allows you to search the tree from a given starting node using a depth-first algorithm.
	 *
	 * The starting node would typically be the root, but it does not have to be.  Also, you can supply a callback
	 * which returns a boolean indicating whether the node should be included in the resultset.  This allows you to
	 * search the tree and filter the resultset according to a certain set of criteria.  The returned resultset is
	 * an array of nodes where the key for each array element is its nodeid.
	 *
	 * @function getTreeDepthFirst
	 * @param TreenodeInterface<NodeValueType>|null $startNode     Defaults to the root node if not supplied
	 * @param callable|null $callback               Defaults to true (return all nodes) if not supplied
	 * @return TreenodeInterface<NodeValueType>[]
	 * @throws NodeNotInTreeException
	 */
	public function getTreeDepthFirst(TreenodeInterface $startNode = null, callable $callback = null): array
	{
		$this->verifyTreeSearchInitialConditions($startNode, $callback);
		return $this->getTreeDepthFirstRecurse($startNode, $callback);
	}

	/**
	 * verifyTreeSearchInitialConditions is called by both getTreeDepthFirst and getTreeBreadthFirst
	 *
	 * If startnode is not supplied as a parameter, it supplies the root node of the tree as a default.  If
	 * startnode is supplied, it verifies that startNode is in the tree.
	 *
	 * If the callback is not supplied, it provides one that returns true for all nodes.  It was tempting to try and
	 * enforce the notion that the callback should return a boolean value, but after thinking about it, I think
	 * that's too restrictive.  The callback is used as an argument to array_filter in the search and array_filter
	 * only needs the callback to return a value that evaluates to true in order to include it in the resultset,
	 * which is different from restricting it to returning a boolean value.
	 *
	 * verifyTreeSearchInitialConditions
	 * @param TreenodeInterface<NodeValueType>|null $startNode
	 * @param callable|null $callback
	 * @throws \Exception
	 */
	protected function verifyTreeSearchInitialConditions(&$startNode = null, callable &$callback = null) : void {

		/**
		 * start at the root of the tree unless a start node was supplied in the parameters
		 */
		if (is_null($startNode)) {
			$startNode = $this->getRoot();
		}

		/**
		 * If the startnode is not in the tree, throw an exception
		 */
		if (!$this->hasNode($startNode)) {
			$nodeid = ($startNode ? $startNode->getNodeId() : null);
			throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(), $nodeid]);
		}

		/**
		 * supply a default callback if not provided in the parameters
		 */
		if (is_null($callback)) {
			$callback = function ($node): bool {
				return true;
			};
		}
	}

	/**
	 * getTreeBreadthFirst allows you to search the tree from a given starting node using a breadth-first algorithm.
	 *
	 * The starting node would typically be the root, but it does not have to be.  Also, you can supply a callback
	 * which returns a boolean indicating whether a node should be included in the resultset.  This allows you to
	 * search the tree and filter the resultset according to a certain set of criteria.  You can also specify a
	 * maximum number of maxLevels down the tree you want to go in the traversal.  If maxLevels is null, the search
	 * goes to the bottom.  The returned resultset is an array of nodes where the key for each array element is its
	 * nodeid.
	 *
	 * @function getTreeBreadthFirst
	 * @param TreenodeInterface<NodeValueType>|null $startNode
	 * @param callable|null $callback
	 * @param int|null $maxLevels
	 * @return TreenodeInterface<NodeValueType>[]
	 * @throws NodeNotInTreeException
	 */
	public function getTreeBreadthFirst(
		$startNode = null,
		callable $callback = null,
		int $maxLevels = null
	): array {
		$this->verifyTreeSearchInitialConditions($startNode, $callback);

		/**
		 * throw an exception if $maxLevels is <= 0
		 */
		if ((!is_null($maxLevels)) && ($maxLevels <= 0)) {
			throw _ExceptionFactory::createException(BadTreesearchLevelsException::class, [$maxLevels]);
		}

		return $this->getTreeBreadthFirstRecurse([$startNode], $callback, $maxLevels);
	}

	/**
	 * getTreeBreadthFirstRecurse does the actual work of traversing the tree.
	 *
	 * @function getTreeBreadthFirstRecurse
	 * @param TreenodeInterface<NodeValueType>[] $result
	 * @param callable $callback
	 * @param int|null $maxLevels
	 * @return TreenodeInterface<NodeValueType>[]
	 */
	protected function getTreeBreadthFirstRecurse(array $result, callable $callback, int $maxLevels = null): array
	{
		/**
		 * filter the existing results using the callback before recursing on the children.
		 */
		$result = array_filter($result, $callback);

		/**
		 * if we have gotten to max maxLevels of search (counting down) then return.  Very important to use strict
		 * equals "===" because maxLevels can be null which is cast to 0 with "=="
		 */
		if ($maxLevels === 0) {
			return $result;
		}

		/**
		 * if maxLevels is being used, decrement it before recursing
		 */
		if (!is_null($maxLevels)) {
			$maxLevels--;
		}

		/**
		 * Get all the children of the current resultset.  This is clumsy but  phpstand does not recognize the array
		 * [$this, 'getChildrenOf'] as callable
		 *
		 * @var callable $callable
		 */
		$callable = [$this, 'getChildrenOf'];
		$allChildren = call_user_func_array('array_merge', array_map($callable, $result));

		/**
		 * if there are children, recurse on them and merge into the current resultset.  If there are no remaining
		 * children, just return the current resultset.
		 */
		if (!empty($allChildren)) {
			return array_merge($result, $this->getTreeBreadthFirstRecurse($allChildren, $callback, $maxLevels));
		} else {
			return $result;
		}
	}

	/**
	 * @function hasLeafWithId
	 * @param ? int $nodeid
	 * @return bool
	 */
	public function hasLeafWithId(int $nodeid = null): bool
	{
		/**
		 * if there is no such nodeid in the tree return false;
		 */
		if (is_null($nodeid) || is_null($this->getNode($nodeid))) {
			return false;
		}

		/**
		 * loop through all nodes.  If we find a node that has nodeid as its parentid then return false.
		 */
		$result = true;
		foreach ($this->nodes as $possibleChild) {
			if ($possibleChild->getParentId() === $nodeid) {
				$result = false;
				break;
			}
		}
		return $result;
	}

	/**
	 * @function hasInteriorNodeWithId
	 * @param ? int $nodeid
	 * @return bool
	 */
	public function hasInteriorNodeWithId(int $nodeid = null): bool
	{
		/**
		 * if there is no such nodeid in the tree return false;
		 */
		if (is_null($nodeid) || is_null($this->getNode($nodeid))) {
			return false;
		}

		/**
		 * loop through all nodes.  If we find a node whose parentid equals nodeid, return true;
		 */
		$result = false;
		foreach ($this->nodes as $node) {
			if ($node->getParentId() === $nodeid) {
				$result = true;
				break;
			}
		}
		return $result;
	}

	/**
	 * getLeaves returns an array of all nodes in the tree that do not have children
	 *
	 * @function getLeaves
	 * @return TreenodeInterface<NodeValueType>[]
	 */
	public function getLeaves(): array
	{
		$result = [];
		foreach ($this->nodes as $node) {
			if ($this->hasLeafWithId($node->getNodeid())) {
				$result[] = $node;
			}
		}
		return $result;
	}

	/**
	 * getInteriorNodes returns an array of all nodes in the tree that have children
	 *
	 * @function getInteriorNodes
	 * @return TreenodeInterface<NodeValueType>[]
	 */
	public function getInteriorNodes(): array
	{
		$result = [];
		foreach ($this->nodes as $node) {
			if (!$this->hasLeafWithId($node->getNodeid())) {
				$result[] = $node;
			}
		}
		return $result;
	}

}