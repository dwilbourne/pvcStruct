<?php

namespace pvc\struct\tree\tree;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreenodeCollectionInterface;
use pvc\struct\collection\Collection;

/**
 * @template TreenodeType of TreenodeInterface
 * @extends Collection<TreenodeType>
 * @implements TreenodeCollectionInterface<TreenodeType>
 */
class TreenodeCollection extends Collection implements TreenodeCollectionInterface
{

}