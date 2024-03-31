<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection\factory;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\collection\factory\CollectionUnorderedFactoryInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\collection\CollectionUnordered;

/**
 * Class CollectionUnorderedFactory
 * @template PayloadType of HasPayloadInterface
 * @extends CollectionFactory<PayloadType, CollectionUnorderedInterface>
 * @implements CollectionUnorderedFactoryInterface<PayloadType>
 */
class CollectionUnorderedFactory extends CollectionFactory implements CollectionUnorderedFactoryInterface
{
    /**
     * makeCollection
     * @return CollectionUnordered<PayloadType>
     */
    public function makeCollection(): CollectionUnordered
    {
        /** @var CollectionUnordered<PayloadType> */
        return new CollectionUnordered();
    }
}
