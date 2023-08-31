<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\collection\CollectionAbstractInterface;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\factory\NodeTypeFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\TreenodeValueObjectInterface;
use pvc\interfaces\struct\tree\tree\TreeAbstractInterface;
use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\tree\factory\TreenodeAbstractFactory;

class TreenodeAbstractFactoryTest extends TestCase
{

    /**
     * @var NodeTypeFactoryInterface|MockObject
     */
    protected NodeTypeFactoryInterface $nodeTypeFactory;

    /**
     * @var CollectionFactoryInterface|MockObject
     */
    protected CollectionFactoryInterface|MockObject $collectionFactory;

    /**
     * @var ValidatorInterface|MockObject
     */
    protected ValidatorInterface $validator;

    /**
     * @var TreeAbstractInterface|MockObject
     */
    protected TreeAbstractInterface $tree;

    /**
     * @var non-negative-int
     */
    protected int $treeId;

    /**
     * @var \pvc\struct\tree\factory\TreenodeAbstractFactory
     */
    protected \pvc\struct\tree\factory\TreenodeAbstractFactory $treenodeAbstractFactory;

    public function setUp(): void
    {
        $this->treeId = 0;
        $this->tree = $this->createMock(TreeAbstractInterface::class);
        $this->tree->method('getTreeId')->willReturn($this->treeId);

        $this->nodeTypeFactory = $this->createMock(NodeTypeFactoryInterface::class);
        $this->collectionFactory = $this->createMock(CollectionFactoryInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);


        $this->treenodeAbstractFactory = new TreenodeAbstractFactory(
            $this->nodeTypeFactory,
            $this->collectionFactory,
            $this->validator
        );
        $this->treenodeAbstractFactory->setTree($this->tree);
    }

    /**
     * testConstruct
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(TreenodeAbstractFactory::class, $this->treenodeAbstractFactory);
    }

    /**
     * testGetCollectionFactory
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::getCollectionFactory
     */
    public function testGetCollectionFactory(): void
    {
        self::assertEquals($this->collectionFactory, $this->treenodeAbstractFactory->getCollectionFactory());
    }

    /**
     * testGetNodeTypeFactory
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::getNodeTypeFactory
     */
    public function testGetNodeTypeFactory(): void
    {
        self::assertEquals($this->nodeTypeFactory, $this->treenodeAbstractFactory->getNodeTypeFactory());
    }

    /**
     * testGetValueValidator
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::GetValueValidator
     */
    public function testGetValueValidator(): void
    {
        self::assertEquals($this->validator, $this->treenodeAbstractFactory->GetValueValidator());
    }

    /**
     * testSetGetTree
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::setTree
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::getTree
     */
    public function testSetGetTree(): void
    {
        $tree = $this->createMock(TreeAbstractInterface::class);
        $this->treenodeAbstractFactory->setTree($tree);
        self::assertEquals($tree, $this->treenodeAbstractFactory->getTree());
    }

    /**
     * testMakeNode
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::makeNode
     */
    public function testMakeNode(): void
    {
        $mockNodeValue = 'foo';

        /** @var TreenodeAbstractInterface|MockObject $mockNode */
        $mockNode = $this->createMock(TreenodeAbstractInterface::class);

        /** @var CollectionAbstractInterface|MockObject $mockCollection */
        $mockCollection = $this->createMock(CollectionAbstractInterface::class);

        $mockValueObject = $this->createMock(TreenodeValueObjectInterface::class);
        $mockValueObject->method('getValue')->willReturn($mockNodeValue);

        $this->collectionFactory->expects($this->once())->method('makeCollection')->willReturn($mockCollection);

        $this->nodeTypeFactory->expects($this->once())->method('makeNodeType')->with(
            $mockValueObject,
            $this->tree,
            $mockCollection
        )->willReturn(
            $mockNode
        );

        $mockNode->expects($this->once())->method('setValueValidator')->with($this->validator);
        $mockNode->expects($this->once())->method('setValue')->with($mockNodeValue);

        self::assertEquals($mockNode, $this->treenodeAbstractFactory->makeNode($mockValueObject));
    }

    /**
     * testMakeCollection
     * @covers \pvc\struct\tree\factory\TreenodeAbstractFactory::makeCollection
     */
    public function testMakeCollection(): void
    {
        $expectedCollection = $this->createMock(CollectionAbstractInterface::class);
        $this->collectionFactory->method('makeCollection')->willReturn($expectedCollection);
        $actualCollection = $this->treenodeAbstractFactory->makeCollection();
        self::assertEquals($expectedCollection, $actualCollection);
    }
}
