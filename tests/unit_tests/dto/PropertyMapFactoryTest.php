<?php

namespace pvcTests\struct\unit_tests\dto;

use PHPUnit\Framework\TestCase;
use pvc\struct\collection\Collection;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyNameException;
use pvc\struct\dto\err\InvalidDtoReflection;
use pvc\struct\dto\PropertyMap;
use pvc\struct\dto\PropertyMapFactory;
use pvc\struct\tree\dto\TreenodeDtoUnordered;
use pvc\struct\tree\node\Treenode;
use ReflectionException;

class PropertyMapFactoryTest extends TestCase
{
    protected PropertyMapFactory $propertyMapFactory;

    /**
     * @var class-string
     */
    protected string $entityClassString;

    /**
     * @var class-string
     * does not implement DtoInterface
     */
    protected string $badDtoClassString;

    /**
     * @var class-string
     * does implement DtoInterface
     */
    protected string $goodDtoClassString;

    public function setUp() : void
    {
        $this->goodDtoClassString = TreenodeDtoUnordered::class;
        $this->badDtoClassString = Collection::class;
        $this->entityClassString = Treenode::class;
        $this->propertyMapFactory = new PropertyMapFactory($this->goodDtoClassString, $this->entityClassString);
    }

    /**
     * @return void
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     * @covers \pvc\struct\dto\PropertyMapFactory::setDtoPublicProperties
     */
    public function testSetDtoPublicPropertiesFailsWithBadClassString(): void
    {
        self::expectException(InvalidDtoReflection::class);
        $this->propertyMapFactory = new PropertyMapFactory($this->badDtoClassString, $this->entityClassString);
    }

    /**
     * @return void
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     * @covers \pvc\struct\dto\PropertyMapFactory::__construct
     * @covers \pvc\struct\dto\PropertyMapFactory::getDTOProperties
     */
    public function testConstruct(): void
    {
        /**
         * property map factory for an unordered treenode dto
         */
        $expectedResult = ['nodeId', 'parentId', 'treeId', 'payload'];
        self::assertEquals($expectedResult, $this->propertyMapFactory->getDTOProperties());
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\PropertyMapFactory::makePropertyMap
     */
    public function testMakePropertyMapThrowsExceptionWithBadPropertyName(): void
    {
        self::expectException(DtoInvalidPropertyNameException::class);
        $badDtoPropertyName = 'badDtoPropertyName';
        $this->propertyMapFactory->makePropertyMap($badDtoPropertyName);
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\PropertyMapFactory::makePropertyMap
     */
    public function testMakePropertyMapThrowsExceptionWithBadEntityGetterName(): void
    {
        self::expectException((DtoInvalidEntityGetterException::class));
        $map = $this->propertyMapFactory->makePropertyMap('nodeId', 'getFoobar');
        unset($map);
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\PropertyMapFactory::makePropertyMap
     */
    public function testMakePropertyMapSucceedsWithGoodEntityGetterName(): void
    {
        $map = $this->propertyMapFactory->makePropertyMap('nodeId', 'getNodeId');
        self::assertTrue($map instanceof PropertyMap);
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\PropertyMapFactory::makePropertyMap
     */
    public function testMakePropertyMapSucceedsWhenEntityGetterFollowsConvention(): void
    {
        $map = $this->propertyMapFactory->makePropertyMap('nodeId');
        self::assertTrue($map instanceof PropertyMap);
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\PropertyMapFactory::makePropertyMap
     */
    public function testMakePropertyMapSetsArrayKeyValueCorrectlyWhenSupplied(): void
    {
        $keyName = 'keyname';
        $map = $this->propertyMapFactory->makePropertyMap('nodeId', null, $keyName);
        self::assertEquals($map->arrayKeyName, $keyName);
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\PropertyMapFactory::makePropertyMap
     */
    public function testMakePropertyMapSetsArrayKeyToPropertyNameWhenOmitted(): void
    {
        $dtoPropertyName = 'nodeId';
        $map = $this->propertyMapFactory->makePropertyMap($dtoPropertyName);
        self::assertEquals($map->arrayKeyName, $dtoPropertyName);
    }
}
