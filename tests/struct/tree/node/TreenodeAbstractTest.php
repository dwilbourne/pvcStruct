<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeIdAndParentIdCannotBeTheSameException;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeAbstract;

class TreenodeAbstractTest extends TestCase
{

	protected int $nodeId;
    protected $node;

    public function setUp(): void
    {
		$this->nodeId = 0;
        $this->node = $this->getMockBuilder(TreenodeAbstract::class)
	                        ->setConstructorArgs([$this->nodeId])
	                        ->getMockForAbstractClass();
    }

	/**
	 * testConstruct
	 * @throws InvalidNodeIdException
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::__construct
	 */
	public function testConstruct() : void
	{
		self::assertInstanceOf(MockObject::class, $this->node);
	}

	/**
     * testSetGetNodeid
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setNodeId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getNodeId
     */
    public function testSetGetNodeid(): void
    {
        self::assertEquals($this->nodeId, $this->node->getNodeId());
    }

    /**
     * testSetBadNodeidThrowsException
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setNodeId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::validateNodeId
     */
    public function testSetBadNodeidThrowsException(): void
    {
        $this->expectException(InvalidNodeIdException::class);
		$this->node->setNodeId(-2);
    }

	/**
	 * testSetNodeIdThrowsExceptionWhenParentWithSameIdIsAlreadySet
	 * @throws InvalidNodeIdException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::setNodeId
	 */
	public function testSetNodeIdThrowsExceptionWhenParentWithSameIdIsAlreadySet() : void
	{
		$this->node->setParentId(3);
		$this->expectException(NodeIdAndParentIdCannotBeTheSameException::class);
		$this->node->setNodeId(3);
	}

    /**
     * testSetGetParentId
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getParentId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParentId
     */
    public function testSetGetParentId(): void
    {
		$nodeId = 0;
        $parentId = 1;
		$this->node->setNodeId($nodeId);

        $this->node->setParentId($parentId);
        self::assertEquals($parentId, $this->node->getParentId());

        $this->node->setParentId(null);
	    self::assertNull($this->node->getParentId());
    }

    /**
     * testSetInvalidParentId
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParentId
     */
    public function testSetInvalidParentId(): void
    {
	    $this->expectException(InvalidNodeIdException::class);
		$this->node->setParentId(-3);
    }

    /**
     * testSetParentIdExceptionWhereParentIdHasSameIdAsChild
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setParentId
     */
    public function testSetParentIdExceptionWhereParentIdHasSameIdAsChild(): void
    {
        $this->node->setNodeId(1);
        $this->expectException(NodeIdAndParentIdCannotBeTheSameException::class);
        $this->node->setParentId(1);
    }

	/**
	 * testIsRootReturnsFalseIfParentIdIsNotSet
	 * @throws InvalidNodeIdException
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::isRoot
	 */

	public function testIsRootReturnsFalseIfParentIdIsNotSet() : void
	{
		$this->node->setNodeId(0);
		self::assertFalse($this->node->isRoot());
	}

	/**
	 * testIsRootReturnsFalseIfParentIdIsNotNull
	 * @throws InvalidNodeIdException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::isRoot
	 */
	public function testIsRootReturnsFalseIfParentIdIsNotNull() : void
	{
		$nodeId = 0;
		$parentId = 1;
		$this->node->setNodeId($nodeId);
		$this->node->setParentId($parentId);
		self::assertFalse($this->node->isRoot());
	}

	/**
	 * testIsRootReturnsTrueOnRootFixture
	 * @throws InvalidNodeIdException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::isRoot
	 */
	public function testIsRootReturnsTrueOnRootFixture() : void
	{
		$this->node->setNodeId(0);
		$this->node->setParentId(null);
		 self::assertTrue($this->node->isRoot());
		//$node = new Treenode(0);
		//$node->setParentId(null);
		//self::assertTrue($node->isRoot());
	}

	/**
	 * testSetTreeidThrowsExceptionOnInvalidTreeid
	 * @throws \Exception
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::setTreeId
	 */
	public function testSetTreeidThrowsExceptionOnInvalidTreeid() : void
	{
		$treeid = -1;
		$this->expectException(InvalidTreeidException::class);
		$this->node->setTreeId($treeid);
	}
    /**
     * testSetGetTreeid
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setTreeId
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getTreeId
     */
    public function testSetGetTreeid(): void
    {
		$treeid = 1;
        $this->node->setTreeId($treeid);
        self::assertEquals($treeid, $this->node->getTreeId());
    }

    /**
     * testSetGetValueValidator
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setValueValidator
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getValueValidator
     */
    public function testSetGetValueValidator(): void
    {
        $validator = $this->createStub(ValidatorInterface::class);
        $this->node->setValueValidator($validator);
        self::assertEquals($validator, $this->node->getValueValidator());
    }

    /**
     * testSetValueWithNoValidatorSet
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setValue
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getValue
     */
    public function testSetValueWithNoValidatorSet(): void
    {
        $anyValue = 1234567;
        // test with no validator set
        $this->node->setValue($anyValue);
        static::assertEquals($anyValue, $this->node->getValue());
    }

    /**
     * testSetGetValueThatPassesValidation
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setValue
     * @covers \pvc\struct\tree\node\TreenodeAbstract::getValue
     */
    public function testSetGetValueThatPassesValidation(): void
    {
        $valueValidator = $this->createMock(ValidatorInterface::class);
        $goodString = 'good string';
        $valueValidator->method('validate')->with($goodString)->willReturn(true);
        $this->node->setValueValidator($valueValidator);
        $this->node->setValue($goodString);
        static::assertEquals($goodString, $this->node->getValue());
    }

    /**
     * testSetValueThatFailsValidation
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeAbstract::setValue
     */
    public function testSetValueThatFailsValidation(): void
    {
        $valueValidator = $this->createMock(ValidatorInterface::class);
        $badString = 'bad string';
        $valueValidator->method('validate')->with($badString)->willReturn(false);
		$this->node->setValueValidator($valueValidator);
        $this->expectException(InvalidNodeValueException::class);
        $this->node->setValue($badString);
    }

	/**
	 * testHydrateDehydrate
	 * @throws InvalidNodeIdException
	 * @throws InvalidNodeValueException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::hydrate
	 * @covers \pvc\struct\tree\node\TreenodeAbstract::dehydrate
	 */
	public function testHydrateDehydrate() : void
	{
		$nodeid = 5;
		$parentid = 1;
		$treeid = 9;
		$value = 'some string';
		$testArray = ['nodeid' => $nodeid, 'parentid' => $parentid, 'treeid' => $treeid, 'value' => $value];

		$this->node->hydrate($testArray);

		self::assertEquals($nodeid, $this->node->getNodeId());
		self::assertEquals($parentid, $this->node->getParentId());
		self::assertEquals($treeid, $this->node->getTreeId());
		self::assertEquals($value, $this->node->getValue());

		$resultArray = $this->node->dehydrate();
		self::assertEqualsCanonicalizing($testArray, $resultArray);
	}

	private function makeNode(int $nodeId,
								int $parentId,
								int $treeId,
								ValidatorInterface $validator,
								int $value) : Treenode {
		$node = new Treenode($nodeId);
		$node->setParentId($parentId);
		$node->setTreeId($treeId);
		$node->setValueValidator($validator);
		$node->setValue($value);
		return $node;
	}
}
