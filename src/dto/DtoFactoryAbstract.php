<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\dto;

use pvc\interfaces\struct\dto\DtoFactoryAbstractInterface;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyValueException;
use pvc\struct\dto\err\PropertMapInvalidKeyException;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class DtoFactoryAbstract
 *
 * This is a dirt-simple mapper/factory, intended really to facilitate testing of data structures / objects such
 * as those found in this library.  Large frameworks and other standalone ORMs obviously have much more
 * sophisticated functionality to get data in and out of the model.
 *
 * a Data Transfer Object (DTO) is a small step up from an associative array, maybe best thought of as an array whose
 * values have prescribed data types. There should not be any logic in the DTO, it should not have
 * setters and getters and all its properties should be public.
 *
 * In order to isolate the data structure (model / entity) from the storage mechanism, this mapper provides the ability
 * to move data from the model to a DTO and then from a DTO to an array, transposing property names and key names
 * as necessary.
 *
 * usage:  using this class, you can make and then hydrate a DTO from an array.  The model can then suck the data from
 * the (publicly accessible) properties and incorporate the DTO's data.  In reverse, this mapper/factory can make
 * a dto and then hydrate the DTO from a model class, or it can move the data from the model directly to an array.
 *
 * If you the name of the property in the dto, the model and the array can all be the same, then it is not necessary
 * to create a property map for that property (assuming the getter in the model is in the camel case format
 * getPropertyName - the code guesses the name of the getter based on the property name).  On the other hand, if the
 * getter does not conform to that practice or if the array key needs to be something different, then add a property
 * map for that property.  So the normal usage is 1) create the factory, 2) add property maps as necessary, 3) do
 * your hydration/dehydration.
 *
 * @template T of ReflectionClass
 */
abstract class DtoFactoryAbstract implements DtoFactoryAbstractInterface
{
    /**
     * @var array<string, PropertyMap>
     * string key is the property name of the dto that is being mapped to an entity property or array key
     */
    protected array $propertyMappings = [];

    /**
     * @var bool
     * internal flag to track whether the propertyMappings property has been fully set.
     */
    private bool $allPropertiesMapped = false;

    /**
     * @param PropertyMapFactory $propertyMapFactory
     */
    public function __construct(
        protected PropertyMapFactory $propertyMapFactory,
    ) {
    }

    public function getPropertyMap(string $dtoPropertyName): PropertyMap
    {
        if (!array_key_exists($dtoPropertyName, $this->propertyMappings)) {
            throw new PropertMapInvalidKeyException($dtoPropertyName);
        }
        return $this->propertyMappings[$dtoPropertyName];
    }

    /**
     * @param array<mixed>|object $source
     * @return DtoInterface
     */
    abstract public function makeDto(array|object $source): DtoInterface;

    /**
     * @param array<mixed>|object $source
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws DtoInvalidPropertyValueException|ReflectionException
     */
    public function hydrate(DtoInterface $dto, array|object $source): void
    {
        /**
         * @var string $dtoPropertyName
         * @var PropertyMap $propertyMap
         */
        foreach ($this->getPropertyMappings() as $dtoPropertyName => $propertyMap) {

            $value = is_array($source) ?
                $this->getValueFromArray($source, $propertyMap) :
                $this->getValueFromEntity($source, $propertyMap);

            try {
                $dto->$dtoPropertyName = $value;

            } catch (Throwable $e) {
                throw new DtoInvalidPropertyValueException($dtoPropertyName, $value, get_class($dto));
            }
        }
    }

    /**
     * @return array<PropertyMap>
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     */
    public function getPropertyMappings(): array
    {
        if (!$this->allPropertiesMapped) {
            $this->createDefaultPropertyMappings();
            $this->allPropertiesMapped = true;
        }
        return $this->propertyMappings;
    }

    /**
     * @return void
     * @throws DtoInvalidEntityGetterException
     * @throws DtoInvalidArrayKeyException
     * @throws ReflectionException
     */
    protected function createDefaultPropertyMappings(): void
    {
        /**
         * @var string $property
         */
        foreach ($this->propertyMapFactory->getDTOProperties() as $property) {
            if (!array_key_exists($property, $this->propertyMappings)) {
                $this->setPropertyMap($property);
            }
        }
    }

    /**
     * @param string $dtoPropertyName
     * @param string $entityGetterMethodName
     * @param string $arrayKeyName
     * @return void
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws ReflectionException
     */
    public function setPropertyMap(
        string $dtoPropertyName,
        string $entityGetterMethodName = '',
        string $arrayKeyName = '',
    ): void {
        $this->propertyMappings[$dtoPropertyName] = $this->propertyMapFactory->makePropertyMap(
            $dtoPropertyName,
            $entityGetterMethodName,
            $arrayKeyName,
        );
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
        foreach ($this->getPropertyMappings() as $propertyMap) {
            $array[$propertyMap->arrayKeyName] = $this->getValueFromEntity($entity, $propertyMap);
        }
        return $array;
    }
}
