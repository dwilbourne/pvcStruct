<?php

namespace pvcTests\struct\unit_tests\di;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\collection\Collection;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\collection\CollectionIndexed;
use pvc\struct\collection\CollectionIndexedFactory;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyValueException;
use pvc\struct\dto\PropertyMapAggregate;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\dto\TreenodeDtoFactory;
use pvc\struct\tree\node\TreenodeCollectionFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreeOrdered;
use ReflectionException;

/**
 * @template PayloadType of HasPayloadInterface
 */
class TreeDefinitionsTest extends TestCase
{
    /**
     * @var ValTesterInterface<PayloadType>|null
     */
    protected ValTesterInterface|null $payloadTester;

    protected Container $container;

    public function setUp() : void
    {
        $this->payloadTester = null;
        $aggregate = new DefinitionAggregate(TreeDefinitions::makeDefinitions($this->payloadTester));
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
        self::assertInstanceOf(Collection::class, $this->container->get(Collection::class));
        self::assertInstanceOf(CollectionFactory::class, $this->container->get(CollectionFactory::class));
        self::assertInstanceOf(TreenodeCollectionFactory::class, $this->container->get('TreenodeCollectionFactoryUnordered'));
        self::assertInstanceOf(TreenodeFactory::class, $this->container->get('TreenodeFactoryUnordered'));
        self::assertInstanceOf(Tree::class, $this->container->get(Tree::class));
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @covers \pvc\struct\tree\di\TreeDefinitions
     */
    public function testSetUpOrdered(): void
    {
        self::assertInstanceOf(CollectionIndexed::class, $this->container->get(CollectionIndexed::class));
        self::assertInstanceOf(CollectionIndexedFactory::class, $this->container->get(CollectionIndexedFactory::class));
        self::assertInstanceOf(TreenodeCollectionFactory::class, $this->container->get('TreenodeCollectionFactoryOrdered'));
        self::assertInstanceOf(TreenodeFactory::class, $this->container->get('TreenodeFactoryOrdered'));
        self::assertInstanceOf(TreeOrdered::class, $this->container->get(TreeOrdered::class));
    }
}
