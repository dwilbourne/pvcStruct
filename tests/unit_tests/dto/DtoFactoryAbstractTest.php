<?php

declare (strict_types = 1);

namespace pvcTests\struct\unit_tests\dto;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\dto\DtoFactoryInterface;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\dto\DtoFactoryAbstract;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyValueException;
use pvc\struct\dto\err\PropertMapInvalidKeyException;
use pvc\struct\dto\PropertyMap;
use pvc\struct\dto\PropertyMapFactory;
use ReflectionClass;
use ReflectionException;

/**
 * @template PayloadType of HasPayloadInterface
 * @template T of ReflectionClass
 */
class DtoFactoryAbstractTest extends TestCase
{
    /**
     * @var DtoFactoryInterface<PayloadType>&MockObject
     */
    protected DtoFactoryInterface&MockObject $dtoFactory;

    /**
     * @var MockObject&PropertyMapFactory<T>
     */
    protected PropertyMapFactory&MockObject $propertyMapFactory;

    /**
     * @var PropertyMap
     */
    protected PropertyMap $propertyMap;

    /**
     * @var string
     */
    protected string $propertyName;

    /**
     * @var DtoFactoryAbstract<T, PayloadType>
     */
    protected DtoFactoryAbstract $dtoFactoryAbstract;

    /**
     * @var DtoInterface<PayloadType>
     */
    protected DtoInterface $dto;

    public function setUp(): void
    {
        $this->dto = new class implements DtoInterface { public string $foo; };

        $this->dtoFactory = $this->createMock(DtoFactoryInterface::class);
        $this->dtoFactory->method('makeDto')->willReturn($this->dto);

        $this->propertyMapFactory = $this->createMock(PropertyMapFactory::class);
        $this->propertyName = 'foo';
        $getter = 'getFoo';
        $arrayKey = 'foo';
        $this->propertyMapFactory->method('getDTOProperties')->willReturn([$this->propertyName]);

        /**
         * cannot use a mock because the class is readonly
         */
        $this->propertyMap = new PropertyMap($this->propertyName, $getter, $arrayKey);

        $this->dtoFactoryAbstract = $this->getMockBuilder(DtoFactoryAbstract::class)
            ->setConstructorArgs([$this->propertyMapFactory])
            ->getMockForAbstractClass();

    }

    /**
     * @return void
     * @covers \pvc\struct\dto\DtoFactoryAbstract::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf($this->dtoFactoryAbstract::class, $this->dtoFactoryAbstract);
    }

    /**
     * @return void
     * @throws PropertMapInvalidKeyException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::getPropertyMap
     */
    public function testGetPropertyMapThrowsExceptionWhenKeyDoesNotExist(): void
    {
        self::expectException(PropertMapInvalidKeyException::class);
        $map = $this->dtoFactoryAbstract->getPropertyMap('quux');
        unset($map);
    }

    /**
     * @return void
     * @throws PropertMapInvalidKeyException
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::setPropertyMap
     * @covers \pvc\struct\dto\DtoFactoryAbstract::getPropertyMap
     */
    public function testSetGetPropertyMap(): void
    {
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $this->dtoFactoryAbstract->setPropertyMap($this->propertyName);
        self::assertEquals($this->propertyMap, $this->dtoFactoryAbstract->getPropertyMap($this->propertyName));
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws ReflectionException
     * @throws DtoInvalidEntityGetterException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::createDefaultPropertyMappings
     * @covers \pvc\struct\dto\DtoFactoryAbstract::getPropertyMappings
     */
    public function testCreateDefaultPropertyMappings(): void
    {
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $expectedResult = ['foo' => $this->propertyMap];
        self::assertEquals($expectedResult, $this->dtoFactoryAbstract->getPropertyMappings());
    }

    /**
     * @return void
     * @depends testCreateDefaultPropertyMappings
     * @covers \pvc\struct\dto\DtoFactoryAbstract::hydrate
     * @covers \pvc\struct\dto\DtoFactoryAbstract::getValueFromArray
     */
    public function testHydrateFromArrayThrowExceptionWithBadArrayKey(): void
    {
        /**
         * dto property name is 'foo'
         * $srcArray has no index 'foo'
         */
        $srcArray = ['bar' => 'baz'];
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $this->expectException(DtoInvalidArrayKeyException::class);
        $this->dtoFactoryAbstract->hydrate($this->dto, $srcArray);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws DtoInvalidPropertyValueException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::hydrate
     * @covers \pvc\struct\dto\DtoFactoryAbstract::getValueFromArray
     */
    public function testHydrateFromArraySucceeds(): void
    {
        /**
         * dto property name is 'foo'
         * $srcArray has index 'foo'
         */
        $srcArray = ['foo' => 'baz'];
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $this->dtoFactoryAbstract->hydrate($this->dto, $srcArray);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @throws DtoInvalidPropertyValueException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::hydrate
     * @covers \pvc\struct\dto\DtoFactoryAbstract::getValueFromEntity
     */
    public function testHydrateFromEntityThrowsExceptionWithBadGetter(): void
    {
        $srcEntity = new class { public function quux() { return 'baz'; } };
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $this->expectException(DtoInvalidEntityGetterException::class);
        $this->dtoFactoryAbstract->hydrate($this->dto, $srcEntity);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws DtoInvalidPropertyValueException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::hydrate
     */
    public function testMakeDtoFailsWhenSourceReturnsIncompatibleValue(): void
    {
        /**
         * foo property of dto is typed as a string, value of array key foo is numeric
         */
        $srcArray = ['foo' => 9];
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $this->expectException(DtoInvalidPropertyValueException::class);
        $this->dtoFactoryAbstract->hydrate($this->dto, $srcArray);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactoryAbstract::makeArrayFromEntity
     */
    public function testMakeArrayFromEntitySucceeds(): void
    {
        $entity = new class { public function getFoo() { return 'bar'; } };
        $this->propertyMapFactory->expects($this->once())->method('makePropertyMap')->willReturn($this->propertyMap);
        $expectedResult = ['foo' => 'bar'];
        self::assertEquals($expectedResult, $this->dtoFactoryAbstract->makeArrayFromEntity($entity));
    }
}
