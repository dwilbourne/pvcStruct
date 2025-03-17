<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\dto;

use pvc\interfaces\struct\dto\DtoFactoryInterface;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\struct\dto\err\DtoClassDefinitionException;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyNameException;
use pvc\struct\dto\err\InvalidDtoReflection;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;

/**
 * Class DtoFactory
 *
 * This is a dirt-simple mapper/factory, intended really to facilitate testing of data structures / objects such
 * as those found in this library.  Large frameworks and other standalone ORMs obviously have much more
 * sophisticated functionality to get data in and out of the model.
 *
 * a Data Transfer Object (DTO) is a small step up from an associative array, maybe best thought of as an array whose
 * values have prescribed data types. There should not be any logic in the DTO, it should not have
 * setters and getters and all its properties should be public readonly.  The property values should be assigned in
 * the constructor.
 *
 * In order to isolate the data structure (model / entity) from the storage mechanism, this mapper provides the ability
 * to move data from the model to a DTO and then from a DTO to an array, transposing property names and key names
 * as necessary.
 *
 * usage:  using this class, you can make a DTO from an array.  The model can then suck the data from
 * the (publicly accessible) properties and incorporate the DTO's data.  In reverse, this mapper/factory can make
 * a dto from a model class, or it can move the data from the model directly to an array.
 *
 * If your name of the property in the dto, the model and the array are all the same, then it is not necessary
 * to create a property map for that property, assuming the getter in the model is in the camel case format
 * "getPropertyName" - the code guesses the name of the getter based on the property name.  On the other hand, if the
 * getter does not conform to that practice or if the array key needs to be something different, then add a property
 * map for that property.  So the normal usage is 1) create the factory, 2) add property maps as necessary, 3) make
 * your dtos.
 *
 * @template T of ReflectionClass
 */
class DtoFactory implements DtoFactoryInterface
{
    /**
     * @var ReflectionClass<DtoInterface>
     * allow visibility from outside the class
     */
    protected ReflectionClass $dtoReflection;

    /**
     * @var ReflectionClass<object>
     */
    protected ReflectionClass $entityReflection;

    /**
     * @var array<string>
     * the getParameters reflection method is guaranteed to return the parameters in the order in which they appear in
     * the constructor.  We also ensure that all the public properties of the dto appear in the constructor args.  So we
     * can use this array to create a new dto and populate its readonly properties.
     */
    protected array $dtoConstructorParamNames;

    /**
     * @var array<string, PropertyMap>
     * string key is the property name of the dto that is being mapped to an entity property or array key
     */
    protected array $propertyMappings;

    /**
     * @param class-string<Dtointerface> $dtoClassName
     * @param class-string<object> $entityClassName
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     */
    public function __construct(string $dtoClassName, string $entityClassName) {
        $this->dtoReflection = new ReflectionClass($dtoClassName);
        /**
         * ensure it is a dto
         */
        if (!$this->dtoReflection->implementsInterface(DtoInterface::class)) {
            throw new InvalidDtoReflection($this->dtoReflection->getName());
        }
        $this->entityReflection = new ReflectionClass($entityClassName);
        $this->setDtoConstructorParamNames();
    }

    /**
     * @return void
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     * checks to ensure that all the public properties are in the list of constructor args.
     */
    protected function setDtoConstructorParamNames(): void
    {

        $dtoPublicProperties = array_map(
            function (ReflectionProperty $value): string {
                return $value->getName();
            },
            $this->dtoReflection->getProperties(ReflectionProperty::IS_PUBLIC),
        );

        $reflectionParams = $this->dtoReflection->getConstructor()?->getParameters() ?? [];
        $callback = function (ReflectionParameter $param) { return $param->getName(); };
        $constructorParams = array_map($callback, $reflectionParams);
        if (array_diff($dtoPublicProperties, $constructorParams)) {
            throw new DtoClassDefinitionException($this->dtoReflection->getName());
        }

        /**
         * the getParameters method is guaranteed to return the parameters in the order in which they appear in the
         * constructor.
         */
        $this->dtoConstructorParamNames = $constructorParams;
    }

    /**
     * @param array<mixed>|object $source
     * @return DtoInterface
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     */
    public function makeDto(array|object $source): DtoInterface
    {
        $args = [];
        foreach ($this->dtoConstructorParamNames as $paramName) {
            $propertyMap = $this->getPropertyMap($paramName);
            $args[$paramName] = is_array($source) ?
                $this->getValueFromArray($source, $propertyMap) :
                $this->getValueFromEntity($source, $propertyMap);
        }
        /** @var DtoInterface $dto */
        $dto = $this->dtoReflection->newInstanceArgs($args);
        return $dto;
    }

    /**
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     */
    protected function makePropertyMap(
        string $dtoPropertyName,
        ?string $entityGetterMethodName = '',
        string $arrayKeyName = '',
    ): PropertyMap {

        if (!in_array($dtoPropertyName, $this->dtoConstructorParamNames)) {
            throw new DtoInvalidPropertyNameException($dtoPropertyName);
        }

        /**
         * if the entityGetterClassName argument is empty, guess that the getter name follows the standard
         * convention for naming getters.
         */
        $entityGetterMethodName = empty($entityGetterMethodName) ?
            'get' . strtoupper(substr($dtoPropertyName, 0, 1)) . substr($dtoPropertyName, 1) :
            $entityGetterMethodName;

        if (!$this->entityReflection->hasMethod($entityGetterMethodName)) {
            throw new DtoInvalidEntityGetterException($entityGetterMethodName, $this->entityReflection->getName());
        }

        $arrayKeyName = empty($arrayKeyName) ? $dtoPropertyName : $arrayKeyName;

        return new PropertyMap($dtoPropertyName, $entityGetterMethodName, $arrayKeyName);
    }

    public function setPropertyMap(        string $dtoPropertyName,
                                           ?string $entityGetterMethodName = '',
                                           string $arrayKeyName = '',
    ): void{
        $this->propertyMappings[$dtoPropertyName] = $this->makePropertyMap($dtoPropertyName, $entityGetterMethodName, $arrayKeyName);
    }

    /**
     * @param string $dtoPropertyName
     * @return PropertyMap
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     */
    public function getPropertyMap(string $dtoPropertyName): PropertyMap
    {
        return $this->propertyMappings[$dtoPropertyName] ?? $this->makePropertyMap($dtoPropertyName);
    }

    /**
     * @param array<mixed> $array
     * @param PropertyMap $propertyMap
     * @return mixed
     * @throws DtoInvalidArrayKeyException
     */
    protected function getValueFromArray(array $array, PropertyMap $propertyMap): mixed
    {
        if (!array_key_exists($propertyMap->arrayKeyName, $array)) {
            throw new DtoInvalidArrayKeyException($propertyMap->arrayKeyName);
        } else {
            return $array[$propertyMap->arrayKeyName];
        }
    }

    /**
     * @param object $entity
     * @param PropertyMap $propertyMap
     * @return mixed
     * @throws DtoInvalidEntityGetterException
     */
    protected function getValueFromEntity(object $entity, PropertyMap $propertyMap): mixed
    {
        try {
            $getterMethodName = $propertyMap->entityGetterMethodName;
            return $entity->$getterMethodName();
        } catch (Throwable $e) {
            throw new DtoInvalidEntityGetterException($propertyMap->entityGetterMethodName, get_class($entity));
        }
    }

    /**
     * @param object $entity
     * @return array<mixed>
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     */
    public function makeArrayFromEntity(object $entity): array
    {
        $array = [];
        foreach ($this->dtoConstructorParamNames as $paramName) {
            $propertyMap = $this->getPropertyMap($paramName);
            $array[$propertyMap->arrayKeyName] = $this->getValueFromEntity($entity, $propertyMap);
        }
        return $array;
    }
}
