<?php

namespace pvcTests\struct\unit_tests\tree\di;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\collection\CollectionOrderedByIndexFactoryInterface;
use pvc\interfaces\struct\collection\CollectionOrderedByIndexInterface;
use pvc\interfaces\struct\collection\CollectionOrderedFactoryInterface;
use pvc\interfaces\struct\collection\CollectionOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeChildCollectionInterface;
use pvc\interfaces\struct\tree\tree\TreenodeCollectionInterface;
use pvc\struct\collection\CollectionOrderedByIndex;
use pvc\struct\collection\CollectionOrderedByIndexFactory;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeChildCollection;
use pvc\struct\tree\node\TreenodeChildCollectionFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreenodeCollection;

class TreeDefinitionsTest extends TestCase
{
    protected Container $container;

    public function setUp(): void
    {
        $aggregate = new DefinitionAggregate(
            TreeDefinitions::makeDefinitions()
        );
        $this->container = new Container($aggregate);
        /**
         * enable autowiring, which recursively evaluates arguments inside the definitions
         */
        $this->container->delegate(new ReflectionContainer());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @covers \pvc\struct\tree\di\TreeDefinitions
     */
    public function testSetUp(): void
    {
        self::assertInstanceOf(
            CollectionInterface::class,
            $this->container->get(CollectionInterface::class)
        );
        self::assertInstanceOf(
            CollectionOrderedByIndexInterface::class,
            $this->container->get(CollectionOrderedByIndex::class)
        );
        self::assertInstanceOf(
            CollectionOrderedByIndexFactoryInterface::class,
            $this->container->get(CollectionOrderedByIndexFactory::class)
        );

        self::assertInstanceOf(
            TreenodeChildCollectionInterface::class,
            $this->container->get(TreenodeChildCollection::class)
        );

        self::assertInstanceOf(
            TreenodeChildCollectionFactoryInterface::class,
            $this->container->get(TreenodeChildCollectionFactory::class)
        );

        self::assertInstanceOf(
            Treenode::class,
            $this->container->get(Treenode::class)
        );

        self::assertInstanceOf(
            TreenodeFactory::class,
            $this->container->get(TreenodeFactory::class)
        );

        self::assertInstanceOf(
            TreenodeCollection::class,
            $this->container->get(TreenodeCollectionInterface::class)
        );

        self::assertInstanceOf(
            Tree::class,
            $this->container->get(Tree::class)
        );
    }
}
