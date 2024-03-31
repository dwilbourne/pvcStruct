<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\collection\factory;

use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;

/**
 * Class CollectionFactory
 * @template PayloadType of HasPayloadInterface
 * @template CollectionType of CollectionAbstractInterface
 * @implements CollectionFactoryInterface<PayloadType, CollectionType>
 */
abstract class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * makeCollection
     * @return CollectionAbstractInterface<PayloadType, CollectionType>
     */
    abstract public function makeCollection(): CollectionAbstractInterface;
}
