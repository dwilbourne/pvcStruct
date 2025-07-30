<?php

namespace pvcTests\struct\unit_tests\tree\di;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionOrdered;
use pvc\struct\collection\CollectionOrderedFactory;
use pvc\struct\tree\di\TreeDefinitions;
use pvcExamples\struct\ordered\TreenodeFactoryOrdered;
use pvcExamples\struct\ordered\TreeOrdered;
use pvcExamples\struct\unordered\TreenodeFactoryUnordered;
use pvcExamples\struct\unordered\TreeUnordered;
use ReflectionException;

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
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @covers \pvc\struct\tree\di\TreeDefinitions
     */
    public function testSetUpUnordered(): void
    {
        self::assertInstanceOf(
            Collection::class,
            $this->container->get(Collection::class)
        );
        self::assertInstanceOf(
            CollectionFactory::class,
            $this->container->get(CollectionFactory::class)
        );
        self::assertInstanceOf(
            TreenodeFactoryUnordered::class,
            $this->container->get(TreenodeFactoryUnordered::class)
        );
        self::assertInstanceOf(
            TreeUnordered::class,
            $this->container->get(TreeUnordered::class)
        );
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @covers \pvc\struct\tree\di\TreeDefinitions
     */
    public function testSetUpOrdered(): void
    {
        self::assertInstanceOf(
            CollectionOrdered::class,
            $this->container->get(CollectionOrdered::class)
        );
        self::assertInstanceOf(
            CollectionOrderedFactory::class,
            $this->container->get(CollectionOrderedFactory::class)
        );
        self::assertInstanceOf(
            TreenodeFactoryOrdered::class,
            $this->container->get(TreenodeFactoryOrdered::class)
        );
        self::assertInstanceOf(
            TreeOrdered::class,
            $this->container->get(TreeOrdered::class)
        );
    }
}
