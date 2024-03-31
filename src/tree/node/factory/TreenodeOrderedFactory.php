<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node\factory;

use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeOrderedFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\node\TreenodeAbstract;
use pvc\struct\tree\node\TreenodeOrdered;

/**
 * Class TreenodeOrderedFactory
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore
 * @extends TreenodeAbstractFactory<PayloadType, TreenodeOrderedInterface, TreeOrderedInterface, CollectionOrderedInterface, TreenodeValueObjectOrderedInterface>
 * @implements TreenodeOrderedFactoryInterface<PayloadType>
 */
class TreenodeOrderedFactory extends TreenodeAbstractFactory implements TreenodeOrderedFactoryInterface
{
    /**
     * makeNode
     * @return TreenodeOrdered<PayloadType>
     * @throws ChildCollectionException
     */
    public function makeNode(): TreenodeAbstract
    {
        /** @var TreenodeOrdered<PayloadType> */
        return new TreenodeOrdered($this->collectionFactory->makeCollection(), $this->getPayloadTester());
    }
}
