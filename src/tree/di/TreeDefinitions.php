<?php

declare(strict_types=1);

namespace pvc\struct\tree\di;

use League\Container\Definition\Definition;
use League\Container\Definition\DefinitionInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\CollectionOrderedFactory;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeFactoryUnordered;
use pvc\struct\tree\tree\TreeOrdered;
use pvc\struct\tree\tree\TreeUnordered;

class TreeDefinitions
{
    /**
     * @return array<int, DefinitionInterface>
     */
    public static function makeDefinitions(): array
    {
        return [

            /**
             * objects necessary to make a plain (unordered) tree
             */
            (new Definition(Collection::class)),
            (new Definition(CollectionFactory::class)),
            (new Definition(TreenodeFactoryUnordered::class))
                ->addArgument(CollectionFactory::class),
            (new Definition(TreeUnordered::class))->addArgument(
                TreenodeFactoryUnordered::class
            ),

            /**
             * objects necessary to make an ordered tree
             */
            (new Definition(CollectionOrdered::class)),
            (new Definition(CollectionOrderedFactory::class)),
            (new Definition(TreenodeFactoryOrdered::class))
                ->addArgument(CollectionOrderedFactory::class),
            (new Definition(TreeOrdered::class))->addArgument(
                TreenodeFactoryOrdered::class
            ),
        ];
    }
}