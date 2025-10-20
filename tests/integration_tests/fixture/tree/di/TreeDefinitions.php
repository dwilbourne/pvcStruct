<?php

declare(strict_types=1);

namespace pvcTests\struct\integration_tests\fixture\tree\di;

use League\Container\Definition\Definition;
use League\Container\Definition\DefinitionInterface;
use pvc\struct\tree\Tree;
use pvc\struct\tree\Treenode;
use pvcTests\struct\unit_tests\tree\fixtures\TestChildCollectionFactory;
use pvcTests\struct\unit_tests\tree\fixtures\TestTreenodeCollectionFactory;
use pvcTests\struct\unit_tests\tree\fixtures\TestTreenodeFactory;

class TreeDefinitions
{
    /**
     * @return array<int, DefinitionInterface>
     */
    public static function makeDefinitions(): array
    {
        return [

            new Definition(Treenode::class, Treenode::class)
                ->addArgument(TestChildCollectionFactory::class),


            new Definition(Tree::class, Tree::class)
            ->addArgument(TestTreenodeFactory::class)
            ->addArgument(TestTreenodeCollectionFactory::class),

        ];
    }
}