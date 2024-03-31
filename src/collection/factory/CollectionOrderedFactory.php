<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection\factory;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\collection\CollectionOrdered;

/**
 * Class CollectionOrderedFactory
 * @template PayloadType of HasPayloadInterface
 * @extends CollectionFactory<PayloadType, CollectionOrderedInterface>
 * @implements CollectionFactoryInterface<PayloadType, CollectionOrdered>
 */
class CollectionOrderedFactory extends CollectionFactory implements CollectionFactoryInterface
{
    /**
     * makeCollection
     * @return CollectionOrdered<PayloadType>
     */
    public function makeCollection(): CollectionOrdered
    {
        /** @var CollectionOrdered<PayloadType> */
        return new CollectionOrdered();
    }
}
