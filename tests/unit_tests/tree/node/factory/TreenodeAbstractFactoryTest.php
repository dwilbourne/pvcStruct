<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\node\factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\interfaces\struct\payload\ValidatorPayloadInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\struct\tree\node\factory\TreenodeAbstractFactory;

class TreenodeAbstractFactoryTest extends TestCase
{

    /**
     * @var CollectionFactoryInterface|MockObject
     */
    protected CollectionFactoryInterface|MockObject $collectionFactory;

    /**
     * @var PayloadTesterInterface|MockObject
     */
    protected PayloadTesterInterface $tester;

    /**
     * @var TreeAbstractInterface|MockObject
     */
    protected TreeAbstractInterface $tree;

    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var TreenodeAbstractFactory
     */
    protected TreenodeAbstractFactory $treenodeAbstractFactory;

    public function setUp(): void
    {
        $this->treeId = 0;
        $this->tree = $this->createMock(TreeAbstractInterface::class);
        $this->tree->method('getTreeId')->willReturn($this->treeId);
        $this->collectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $this->tester = $this->createMock(PayloadTesterInterface::class);


        $this->treenodeAbstractFactory = $this->getMockBuilder(TreenodeAbstractFactory::class)
                                              ->setConstructorArgs([$this->collectionFactory, $this->tester])
                                              ->getMockForAbstractClass();
        $this->treenodeAbstractFactory->setTree($this->tree);
    }

    /**
     * testConstruct
     * @covers TreenodeAbstractFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeAbstractFactory::class, $this->treenodeAbstractFactory);
    }

    /**
     * testGetCollectionFactory
     * @covers TreenodeAbstractFactory::setCollectionFactory
     * @covers TreenodeAbstractFactory::getCollectionFactory
     */
    public function testGetCollectionFactory(): void
    {
        self::assertEquals($this->collectionFactory, $this->treenodeAbstractFactory->getCollectionFactory());
    }

    /**
     * testGetValueValidator
     * @covers TreenodeAbstractFactory::getPayloadTester
     */
    public function testGetPayloadValidator(): void
    {
        self::assertEquals($this->tester, $this->treenodeAbstractFactory->getPayloadTester());
    }

    /**
     * testSetGetTree
     * @covers TreenodeAbstractFactory::setTree
     * @covers TreenodeAbstractFactory::getTree
     */
    public function testSetGetTree(): void
    {
        $tree = $this->createMock(TreeAbstractInterface::class);
        $this->treenodeAbstractFactory->setTree($tree);
        self::assertEquals($tree, $this->treenodeAbstractFactory->getTree());
    }
}
