<?php

declare(strict_types=1);

namespace pvc\struct\tree\node;


use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * Treenode is a base class supporting node operations in a tree
 * 
 * @template NodeValueType
 * @implements TreenodeInterface<NodeValueType>
 */
class Treenode implements TreenodeInterface
{
	/**
	 * @phpstan-use TreenodeTrait<NodeValueType>
	 */
    use TreenodeTrait;

    /**
     * Treenode constructor.
     * @param int $nodeid
     * @throws \pvc\struct\tree\err\InvalidNodeIdException
     */
    public function __construct(int $nodeid)
    {
        $this->setNodeId($nodeid);
    }
}
