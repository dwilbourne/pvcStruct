<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node_value_object\factory;

use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactoryInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\struct\tree\node_value_object\TreenodeValueObjectOrdered;

/**
 * Class TreenodeValueObjectOrderedFactory
 * @template PayloadType of HasPayloadInterface
 * @implements TreenodeValueObjectOrderedFactoryInterface<PayloadType>
 */
class TreenodeValueObjectOrderedFactory implements TreenodeValueObjectOrderedFactoryInterface
{

    /**
     * makeValueObject
     * @return TreenodeValueObjectOrdered<PayloadType>
     */
    public function makeValueObject(): TreenodeValueObjectInterface
    {
        /** @var TreenodeValueObjectOrdered<PayloadType> */
        return new TreenodeValueObjectOrdered();
    }
}
