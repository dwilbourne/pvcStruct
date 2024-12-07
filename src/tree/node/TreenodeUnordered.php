<?php

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOUnorderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;

/**
 * class TreenodeUnordered
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore -- generics must be all on the same line in order to be processed correctly by phpstan
 * @extends TreenodeAbstract<PayloadType, TreenodeUnorderedInterface, TreeUnorderedInterface, CollectionUnorderedInterface, TreenodeDTOUnorderedInterface>
 * @implements TreenodeUnorderedInterface<PayloadType>
 */
class TreenodeUnordered extends TreenodeAbstract implements TreenodeUnorderedInterface
{
}
