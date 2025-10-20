<?php

namespace pvcTests\struct\integration_tests\fixture\tree;

use pvc\interfaces\struct\tree\tree\TreeInterface;

use \pvc\struct\tree\Tree as GenericTree;

/**
 * @extends GenericTree<non-negative-int, non-negative-int, Treenode>
 * @implements TreeInterface<non-negative-int, non-negative-int, Treenode>
 */
class Tree extends GenericTree implements TreeInterface
{

}