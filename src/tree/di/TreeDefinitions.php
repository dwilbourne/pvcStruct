<?php

declare(strict_types=1);

namespace pvc\struct\tree\di;

use League\Container\Argument\LiteralArgument;
use League\Container\Definition\Definition;
use League\Container\Definition\DefinitionInterface;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\dto\DtoFactoryAbstractInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoCollectionFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionIndexed;
use pvc\struct\collection\CollectionIndexedFactory;
use pvc\struct\dto\DtoFactoryAbstract;
use pvc\struct\dto\PropertyMapFactory;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\dto\TreenodeDtoCollectionFactory;
use pvc\struct\tree\dto\TreenodeDtoFactory;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeCollectionFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\tree\Tree;

/**
 * @template PayloadType of HasPayloadInterface
 */
class TreeDefinitions
{
    /**
     * @param bool $ordered
     * @param ValTesterInterface<PayloadType>|null $payloadTester
     * @return array<int, DefinitionInterface>
     */
    public static function makeDefinitions(bool $ordered = false, ?ValTesterInterface $payloadTester = null): array
    {
        return [
            /**
             * collections and their factories are either ordered or unordered
             */
            $ordered ?
                new Definition(CollectionInterface::class, CollectionIndexed::class) :
                new Definition(CollectionInterface::class, Collection::class),

            $ordered ?
                new Definition(CollectionFactoryInterface::class, CollectionIndexedFactory::class) :
                new Definition(CollectionFactoryInterface::class, CollectionFactory::class),

            /**
             * definitions needed to make an array of TreenodeDto
             */

            (new Definition(TreenodeDtoCollectionFactoryInterface::class, TreenodeDtoCollectionFactory::class))->addArgument(CollectionFactoryInterface::class),

            (new Definition('TreenodePropertyMapFactory', PropertyMapFactory::class))
                /**
                 * the first argument MUST be the concrete TreenodeDto class because reflection is going to look through
                 * its public properties.  TreenodeDtoInterface has no public properties of course.
                 */
                ->addArgument(new LiteralArgument(TreenodeDto::class))
                ->addArgument(new LiteralArgument(Treenode::class)),

            (new Definition(DtoFactoryAbstractInterface::class, DtoFactoryAbstract::class))
                ->addArgument(PropertyMapFactory::class),

            (new Definition(TreenodeDtoFactoryInterface::class, TreenodeDtoFactory::class))
                ->addArgument('TreenodePropertyMapFactory'),

            /**
             * definitions to make Tree nodes
             */

            (new Definition(TreenodeFactoryInterface::class, TreenodeFactory::class))
                ->addArgument(TreenodeCollectionFactoryInterface::class)
                ->addArgument($payloadTester),


            (new Definition(TreenodeCollectionFactoryInterface::class, TreenodeCollectionFactory::class))
                ->addArgument(CollectionFactoryInterface::class),

            /**
             * definitions to make a Tree
             */

            (new Definition(TreeInterface::class, Tree::class))
                ->addArgument(TreenodeFactoryInterface::class)
                ->addArgument(TreenodeDtoCollectionFactoryInterface::class),
        ];
    }
}