<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;

/**
 * Class CollectionUnordered
 * @template PayloadType of HasPayloadInterface
 * @extends CollectionAbstract<PayloadType, CollectionUnorderedInterface>
 * @implements CollectionUnorderedInterface<PayloadType>
 */
class CollectionUnordered extends CollectionAbstract implements CollectionUnorderedInterface
{
}
