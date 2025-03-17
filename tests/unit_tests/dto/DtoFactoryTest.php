<?php

declare (strict_types = 1);

namespace pvcTests\struct\unit_tests\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\dto\DtoFactoryInterface;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\struct\dto\DtoFactory;
use pvc\struct\dto\err\DtoClassDefinitionException;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyNameException;
use pvc\struct\dto\err\InvalidDtoReflection;
use ReflectionException;
use stdClass;
use TypeError;

class DtoFactoryTest extends TestCase
{
    /**
     * @var DtoFactoryInterface
     */
    protected DtoFactoryInterface $dtoFactory;

    protected $entityClass;

    protected $dtoClass;

    public function setUp(): void
    {
        $this->dtoClass = new readonly class(4) implements DtoInterface { public function __construct(public int $foo) {} };

        /**
         * note that getBar is the getter, not the expected 'getFoo'
         */
        $this->entityClass = new class { public function getFoo(): int { return 11; } };

        $this->dtoFactory = new DtoFactory(get_class($this->dtoClass), get_class($this->entityClass));
    }

    /**
     * @return void
     * @covers \pvc\struct\dto\DtoFactory::__construct
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf($this->dtoFactory::class, $this->dtoFactory);
    }

    /**
     * @return void
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::__construct
     */
    public function testConstructThrowsExceptionWithBadDtoClass(): void
    {
        $badDtoClass = new stdClass();
        $this->expectException(InvalidDtoReflection::class);
        $dtoFactory = new DtoFactory(get_class($badDtoClass), get_class($this->entityClass));
    }

    /**
     * @return void
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::setDtoConstructorParamNames
     */
    public function testSetDtoConstructorParamNamesFailsWhenNotAllPublicPropertiesAreInConstructor(): void
    {
        $badDtoClass = new readonly class(4) implements DtoInterface
            {
                public string $bar;
                public function __construct(public int $foo){}
            };
        $this->expectException(DtoClassDefinitionException::class);
        $dtoFactory = new DtoFactory(get_class($badDtoClass), get_class($this->entityClass));
    }

    /**
     * @return void
     * @covers \pvc\struct\dto\DtoFactory::makeDto
     * @covers \pvc\struct\dto\DtoFactory::setDtoConstructorParamNames
     * @covers \pvc\struct\dto\DtoFactory::setPropertyMap
     * @covers \pvc\struct\dto\DtoFactory::makePropertyMap
     * @covers \pvc\struct\dto\DtoFactory::getPropertyMap
     * @covers \pvc\struct\dto\DtoFactory::getValueFromArray
     */
    public function testMakeDtoFromArrayThrowExceptionWithBadArrayKey(): void
    {
        /**
         * dto property name is 'foo'
         * $srcArray has no index 'foo'
         */
        $srcArray = ['bar' => 7];
        $this->expectException(DtoInvalidArrayKeyException::class);
        $this->dtoFactory->makeDto($srcArray);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::makeDto
     * @covers \pvc\struct\dto\DtoFactory::getValueFromArray
     */
    public function testMakeDtoFromArraySucceeds(): void
    {
        /**
         * dto property name is 'foo'
         * $srcArray has index 'foo'
         */
        $srcArray = ['foo' => 9];
        $dto = $this->dtoFactory->makeDto($srcArray);
        self::assertEquals(9, $dto->foo);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::makeDto
     * @covers \pvc\struct\dto\DtoFactory::getValueFromEntity
     */
    public function testMakeDtoFromEntityThrowsExceptionWithBadGetter(): void
    {
        $entityClassWithNonStandardGetter = new class { public function getBar(): int { return 11; } };
        $this->expectException(DtoInvalidEntityGetterException::class);
        $this->dtoFactory->makeDto($entityClassWithNonStandardGetter);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::setPropertyMap
     * @covers \pvc\struct\dto\DtoFactory::makePropertyMap
     */
    public function testMakeDtoFromEntitySucceedsWithNonStandardGetter(): void
    {
        $entityClassWithNonStandardGetter = new class { public function getBar(): int { return 11; } };
        $dtoFactory = new DtoFactory(get_class($this->dtoClass), get_class($entityClassWithNonStandardGetter));
        $dtoFactory->setPropertyMap('foo', 'getBar', 'foo');
        $dto = $dtoFactory->makeDto($entityClassWithNonStandardGetter);
        /**
         * entity class returns 11
         */
        self::assertEquals(11, $dto->foo);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::makePropertyMap
     */
    public function testMakeDtoFromEntityFailsWithPropertyMapHavingBadEntityGetter(): void
    {
        /**
         * no such getter in entity class
         */
        self::expectException(DtoInvalidEntityGetterException::class);
        $this->dtoFactory->setPropertyMap('foo', 'getQuux', 'foo');
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::makeDto
     * @covers \pvc\struct\dto\DtoFactory::setPropertyMap
     * @covers \pvc\struct\dto\DtoFactory::makePropertyMap
     * @covers \pvc\struct\dto\DtoFactory::getValueFromEntity
     */
    public function testMakeDtoFromEntitySucceeds(): void
    {
        $dto = $this->dtoFactory->makeDto($this->entityClass);
        /**
         * entity class returns 11
         */
        self::assertEquals(11, $dto->foo);
    }

    /**
     * @return void
     * @covers \pvc\struct\dto\DtoFactory::makePropertyMap
     */
    public function testMakePropertyMapFailsWithBadPropertyName(): void
    {
        self::expectException(DtoInvalidPropertyNameException::class);
        /**
         * no such dto property quux
         */
        $this->dtoFactory->setPropertyMap('quux', 'getBar', 'foo');
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::makeDto
     * @covers \pvc\struct\dto\DtoFactory::getValueFromEntity
     */
    public function testMakeDtoFailsWhenSourceReturnsIncompatibleValue(): void
    {
        /**
         * foo property of dto is typed as int, value of array key foo is string
         */
        $srcArray = ['foo' => 'bar'];
        $this->expectException(TypeError::class);
        $this->dtoFactory->makeDto($srcArray);
    }

    /**
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     * @covers \pvc\struct\dto\DtoFactory::makeArrayFromEntity
     */
    public function testMakeArrayFromEntitySucceeds(): void
    {
        $expectedResult = ['foo' => 11];
        $actualResult = $this->dtoFactory->makeArrayFromEntity($this->entityClass);
        self::assertEquals($expectedResult, $actualResult);
    }
}
