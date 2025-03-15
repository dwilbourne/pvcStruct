<?php

declare(strict_types=1);

namespace pvc\struct\tree\di;

use League\Container\Argument\LiteralArgument;
use League\Container\Definition\Definition;
use League\Container\Definition\DefinitionInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoFactoryInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionIndexed;
use pvc\struct\collection\CollectionIndexedFactory;
use pvc\struct\dto\PropertyMapFactory;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\dto\TreenodeDtoFactory;
use pvc\struct\tree\node\Treenode;
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
             * definitions to make Dtos (Data Transfer Objects)
             */

            (new Definition('TreenodePropertyMapFactory', PropertyMapFactory::class))
                /**
                 * the first argument MUST be the concrete TreenodeDto class because reflection is going to look through
                 * its public properties.  TreenodeDtoInterface has no public properties of course.
                 */
                ->addArgument(new LiteralArgument(TreenodeDto::class))
                ->addArgument(new LiteralArgument(Treenode::class)),

            // (new Definition(DtoFactoryAbstract::class))->addArgument(PropertyMapFactory::class),

            (new Definition(TreenodeDtoFactoryInterface::class, TreenodeDtoFactory::class))
                ->addArgument('TreenodePropertyMapFactory'),

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