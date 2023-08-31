<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\integration_tests\tree\fixture;

use pvc\interfaces\struct\collection\CollectionAbstractInterface as CollectionType;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\struct\collection\CollectionOrdered;

/**
 * Class CollectionOrderedFactory
 */
class CollectionOrderedFactory implements CollectionFactoryInterface
{

    /**
     * makeCollection
     * @return CollectionType
     */
    public function makeCollection(): CollectionType
    {
        return new CollectionOrdered();
    }
}
