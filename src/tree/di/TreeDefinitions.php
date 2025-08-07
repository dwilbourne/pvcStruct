<?php

declare(strict_types=1);

namespace pvc\struct\tree\di;

use League\Container\Definition\Definition;
use League\Container\Definition\DefinitionInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\collection\CollectionOrderedByIndexFactoryInterface;
use pvc\interfaces\struct\collection\CollectionOrderedByIndexInterface;
use pvc\interfaces\struct\collection\CollectionOrderedFactoryInterface;
use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\struct\tree\tree\TreenodeCollectionInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionOrderedByIndex;
use pvc\struct\collection\CollectionOrderedByIndexFactory;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeChildCollection;
use pvc\struct\tree\node\TreenodeChildCollectionFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreenodeCollection;

class TreeDefinitions
{
    /**
     * @return array<int, DefinitionInterface>
     */
    public static function makeDefinitions(): array
    {
        return [
            /**
             * map interfaces to implementations
             */

            new Definition(CollectionInterface::class, Collection::class),

            new Definition(CollectionOrderedByIndexInterface::class, CollectionOrderedByIndex::class),
            new Definition(CollectionOrderedByIndexFactoryInterface::class, CollectionOrderedByIndexFactory::class),

            new Definition(TreenodeChildCollectionInterface::class, TreenodeChildCollection::class),
            new Definition(TreenodeChildCollectionFactoryInterface::class, TreenodeChildCollectionFactory::class),

            new Definition(TreenodeInterface::class, Treenode::class),
            new Definition(TreenodeFactoryInterface::class, TreenodeFactory::class),
            new Definition(TreenodeCollectionInterface::class, TreenodeCollection::class),

            new Definition(TreeInterface::class, Tree::class),

            /**
             * definitions for the implementations
             */
            new Definition(Collection::class),
            new Definition(CollectionOrderedByIndex::class),
            new Definition(CollectionOrderedByIndexFactory::class)
                ->setShared(),

            new Definition(TreenodeChildCollection::class),
            new Definition(TreenodeChildCollectionFactory::class)
                ->setShared(),

            new Definition(Treenode::class, Treenode::class)
                ->addArgument(TreenodeChildCollectionFactoryInterface::class),

            new Definition(TreenodeFactory::class)
                ->addArgument(TreenodeChildCollectionFactoryInterface::class)
                /**
                 * why is the argument to setShared required here and not elsewhere
                 * (phpstan picks it up)
                 */
                ->setShared(true),

            new Definition(TreenodeCollection::class),

            new Definition(Tree::class)
                /**
                 * should be able to type hint these as interfaces, but I think there's
                 * a bug in the League's resolver code.  Type hinting the interface
                 * resolves down to the concrete classes, but *without* any arguments
                 * that are part of the definitions.  in other words, there is a call to
                 * $container->get(TreenodeFactory::class) but it dies because it can't find
                 * the argument for the constructor which is defined above.
                 */
                ->addArgument(TreenodeFactory::class)
                ->addArgument(TreenodeCollection::class),
        ];
    }
}