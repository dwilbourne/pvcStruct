<?php

namespace pvcTests\struct\integration_tests\fixture\tree;

use pvc\interfaces\struct\tree\node\TreenodeCollectionInterface;
use pvc\struct\collection\Collection;

/**
 * @extends Collection<non-negative-int, Treenode>
 * @implements TreenodeCollectionInterface<non-negative-int, Treenode>
 */
class TreenodeCollection extends Collection implements TreenodeCollectionInterface
{

}