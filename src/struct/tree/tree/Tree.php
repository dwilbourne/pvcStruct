<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\tree;

use pvc\struct\tree\err\_ExceptionFactory;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\node\Treenode;

/**
 * Class Tree
 *
 * by convention, root node of a tree has null as a parent (see treenode object).
 *
 * @template NodeValueType
 * @extends TreeAbstract<TreenodeInterface>
 * @implements TreeInterface<NodeValueType>
 */
class Tree extends TreeAbstract implements TreeInterface
{

	/**
	 * addNode adds a single node into the tree.
	 *
	 * @param TreenodeInterface<NodeValueType> $node
	 * @throws \pvc\struct\tree\err\AlreadySetRootException
	 */
	public function addNode($node): void
	{
		/**
		 * This seems like a waste, except that the add method for TreeOrdered also uses addNodeToNodelistAndSetRoot and then
		 * does some more stuff.  So the common code between the two classes is shared in the addNodeToNodelistAndSetRoot method
		 * kept in TreeAbstract
		 */
		$this->addNodeToNodelistAndSetRoot($node);
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
	     * does some more stuff so the common code between the two classes is shared in the addNodeToNodelistAndSetRoot method
	     * kept in TreeAbstract
	     */
		$this->addNodesToNodelistAndSetRoot($nodeArray, TreenodeInterface::class);
    }


	/**
	 * @function getChildrenOf
	 * @param TreenodeInterface<NodeValueType> $parent
	 * @return TreenodeInterface<NodeValueType>[]
	 * @throws \Exception
	 */
    public function getChildrenOf($parent): array
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
    public function getParentOf($node): ?TreenodeInterface
    {
        if (!$this->hasNode($node)) {
            throw _ExceptionFactory::createException(NodeNotInTreeException::class, [$this->getTreeId(),
	            $node->getNodeId
	        ()]);
        }
        return $this->nodes[$node->getParentId()];
    }


}
