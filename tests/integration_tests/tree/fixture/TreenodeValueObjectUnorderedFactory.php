<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcTests\struct\integration_tests\tree\fixture;

use pvc\interfaces\struct\tree\factory\TreenodeValueObjectFactoryInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered;

/**
 * Class TreenodeValueObjectUnorderedFactory
 */
class TreenodeValueObjectUnorderedFactory implements TreenodeValueObjectFactoryInterface
{

    /**
     * makeValueObject
     * @return TreenodeValueObjectInterface
     */
    public function makeValueObject(): TreenodeValueObjectInterface
    {
        return new TreenodeValueObjectUnordered();
    }
}