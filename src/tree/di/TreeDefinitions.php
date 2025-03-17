<?php

declare(strict_types=1);

namespace pvc\struct\tree\di;

use League\Container\Definition\Definition;
use League\Container\Definition\DefinitionInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionIndexed;
use pvc\struct\collection\CollectionIndexedFactory;
use pvc\struct\tree\node\TreenodeCollectionFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreeOrdered;

/**
 * @template PayloadType of HasPayloadInterface
 */
class TreeDefinitions
{
    /**
     * @param ValTesterInterface<PayloadType>|null $payloadTester
     * @return array<int, DefinitionInterface>
     */
    public static function makeDefinitions(?ValTesterInterface $payloadTester): array
    {
        return [

            /**
             * objects necessary to make a plain (unordered) tree
             */
            (new Definition(Collection::class)),
            (new Definition(CollectionFactory::class)),
            (new Definition('TreenodeCollectionFactoryUnordered', TreenodeCollectionFactory::class))
                ->addArgument(CollectionFactory::class),
            (new Definition('TreenodeFactoryUnordered', TreenodeFactory::class))
                ->addArgument('TreenodeCollectionFactoryUnordered')
                ->addArgument($payloadTester),
            (new Definition(Tree::class))->addArgument('TreenodeFactoryUnordered'),

            /**
             * objects necessary to make an ordered tree
             */
            (new Definition(CollectionIndexed::class)),
            (new Definition(CollectionIndexedFactory::class)),
            (new Definition('TreenodeCollectionFactoryOrdered', TreenodeCollectionFactory::class))
                ->addArgument(CollectionIndexedFactory::class),
            (new Definition('TreenodeFactoryOrdered', TreenodeFactory::class))
                ->addArgument('TreenodeCollectionFactoryOrdered')
                ->addArgument($payloadTester),

            (new Definition(TreeOrdered::class))->addArgument('TreenodeFactoryOrdered'),
        ];
    }
}