<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\integration_tests\tree\fixture;

use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\struct\collection\CollectionUnordered;

/**
 * Class CollectionUnorderedFactory
 */
class CollectionUnorderedFactory implements CollectionFactoryInterface
{
    public function makeCollection(): CollectionUnordered
    {
        return new CollectionUnordered();
    }
}