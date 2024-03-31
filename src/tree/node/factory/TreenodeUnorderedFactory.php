<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\node\factory;

use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\factory\TreenodeUnorderedFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectUnorderedInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\struct\tree\err\ChildCollectionException;
use pvc\struct\tree\node\TreenodeAbstract;
use pvc\struct\tree\node\TreenodeUnordered;

/**
 * Class TreenodeUnorderedFactory
 * @template PayloadType of HasPayloadInterface
 * @phpcs:ignore
 * @extends TreenodeAbstractFactory<PayloadType, TreenodeUnorderedInterface, TreeUnorderedInterface, CollectionUnorderedInterface, TreenodeValueObjectUnorderedInterface>
 * @implements TreenodeUnorderedFactoryInterface<PayloadType>
 */
class TreenodeUnorderedFactory extends TreenodeAbstractFactory implements TreenodeUnorderedFactoryInterface
{
    /**
     * makeNode
     * @return TreenodeUnordered<PayloadType>
     * @throws ChildCollectionException
     */
    public function makeNode(): TreenodeAbstract
    {
        /** @var TreenodeUnordered<PayloadType> */
        return new TreenodeUnordered($this->collectionFactory->makeCollection(), $this->getPayloadTester());
    }
}
