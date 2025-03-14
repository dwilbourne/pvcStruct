<?php

namespace pvcTests\struct\unit_tests\di;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use pvc\interfaces\struct\collection\CollectionFactoryInterface;
use pvc\interfaces\struct\collection\CollectionInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoCollectionFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeCollectionFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionIndexed;
use pvc\struct\collection\CollectionIndexedFactory;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyValueException;
use pvc\struct\dto\PropertyMapFactory;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\dto\TreenodeDtoCollectionFactory;
use pvc\struct\tree\dto\TreenodeDtoFactory;
use pvc\struct\tree\node\TreenodeCollectionFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\tree\Tree;
use ReflectionException;

class TreeDefinitionsTest extends TestCase
{
    protected Container $container;

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws DtoInvalidPropertyValueException
     * @covers \pvc\struct\tree\di\TreeDefinitions
     */
    public function testSetUpUnordered(): void
    {
        $ordered = false;
        $this->makeContainer($ordered);
        self::assertInstanceOf(Collection::class, $this->container->get(CollectionInterface::class));
        self::assertInstanceOf(CollectionFactory::class, $this->container->get(CollectionFactoryInterface::class));
        self::assertInstanceOf(TreenodeDtoCollectionFactory::class, $this->container->get(TreenodeDtoCollectionFactoryInterface::class));
        self::assertInstanceOf(PropertyMapFactory::class, $this->container->get('TreenodePropertyMapFactory'));
        self::assertInstanceOf(TreenodeDtoFactory::class, $this->container->get(TreenodeDtoFactoryInterface::class));
        self::assertInstanceOf(TreenodeCollectionFactory::class, $this->container->get(TreenodeCollectionFactoryInterface::class));
        self::assertInstanceOf(TreenodeFactory::class, $this->container->get(TreenodeFactoryInterface::class));
        self::assertInstanceOf(Tree::class, $this->container->get(TreeInterface::class));
    }

    public function makeContainer(bool $ordered) : void
    {
        $aggregate = new DefinitionAggregate(TreeDefinitions::makeDefinitions($ordered));
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
    public function testSetUpOrdered(): void
    {
        $ordered = true;
        $this->makeContainer($ordered);
        self::assertInstanceOf(CollectionIndexed::class, $this->container->get(CollectionInterface::class));
        self::assertInstanceOf(CollectionIndexedFactory::class, $this->container->get(CollectionFactoryInterface::class));
    }
}
