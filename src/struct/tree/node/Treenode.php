<?php declare(strict_types = 1);

namespace pvc\struct\tree\node;

use pvc\struct\tree\iface\node\TreenodeInterface;
use pvc\validator\base\ValidatorInterface;
use pvc\validator\numeric\ValidatorIntegerNonNegative;

/**
 * Treenode is a base class supporting node operations in a tree
 */
class Treenode implements TreenodeInterface
{
    use TreenodeTrait;

    /**
     * Treenode constructor.
     * @param int $nodeid
     * @throws err\InvalidNodeIdException
     */
    public function __construct(int $nodeid)
    {
        $this->setNodeId($nodeid);
    }

    /**
     * @function unsetReferences
     */
    public function unsetReferences() : void
    {
        unset($this->treeid);
        unset($this->parentid);
        unset($this->value);
        // do not unset nodeid - node must remain in a valid state
        // unset($this->nodeid);
    }
}
