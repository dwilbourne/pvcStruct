<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;

/**
 * Class CollectionUnordered
 * @template ElementType
 * @extends CollectionAbstract<ElementType, CollectionUnorderedInterface>
 * @implements CollectionUnorderedInterface<ElementType>
 */
class CollectionUnordered extends CollectionAbstract implements CollectionUnorderedInterface
{
}
