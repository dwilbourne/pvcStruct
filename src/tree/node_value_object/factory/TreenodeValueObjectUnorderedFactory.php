<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node_value_object\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node_value_object\factory\TreenodeValueObjectUnorderedFactoryInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\struct\tree\node_value_object\TreenodeValueObjectUnordered;

/**
 * Class TreenodeValueObjectUnorderedFactory
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeValueObjectUnorderedFactoryInterface<PayloadType>
 */
class TreenodeValueObjectUnorderedFactory implements TreenodeValueObjectUnorderedFactoryInterface
{

    /**
     * makeValueObject
     * @return TreenodeValueObjectUnordered<PayloadType>
     */
    public function makeValueObject(): TreenodeValueObjectInterface
    {
        /** @var TreenodeValueObjectUnordered<PayloadType> */
        return new TreenodeValueObjectUnordered();
    }
}
