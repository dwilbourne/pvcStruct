<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;

/**
 * Class TreenodeFactory
 *
 * @template TreenodeType of TreenodeInterface
 * @implements TreenodeFactoryInterface<TreenodeType>
 */
class TreenodeFactory implements TreenodeFactoryInterface
{
    /**
     * @param  TreenodeChildCollectionFactoryInterface<TreenodeType>  $collectionFactory
     */
    public function __construct(
        protected TreenodeChildCollectionFactoryInterface $collectionFactory,
    ) {
    }

    /**
     * @return Treenode<TreenodeType>
     */
    public function makeNode(): Treenode
    {
        return new Treenode($this->collectionFactory);
    }
}
