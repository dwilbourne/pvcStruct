<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\dto;

use pvc\interfaces\struct\dto\DtoInterface;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyNameException;
use pvc\struct\dto\err\InvalidDtoReflection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class PropertyMapFactory
 */
class PropertyMapFactory
{
    /**
     * @var ReflectionClass<object>
     */
    protected ReflectionClass $dtoReflection;

    /**
     * @var ReflectionClass<object>
     */
    protected ReflectionClass $entityReflection;

    /**
     * @var array<string>
     */
    protected array $dtoPublicProperties;

    /**
     * @param class-string $dtoClassName
     * @param class-string $entityClassName
     * @throws ReflectionException
     * @throws InvalidDtoReflection
     */
    public function __construct(string $dtoClassName, string $entityClassName)
    {
        $this->dtoReflection = new ReflectionClass($dtoClassName);
        $this->entityReflection = new ReflectionClass($entityClassName);
        $this->setDtoPublicProperties();
    }

    /**
     * @return void
     * @throws InvalidDtoReflection
     * @throws ReflectionException
     */
    protected function setDtoPublicProperties(): void
    {
        if (!$this->dtoReflection->implementsInterface(DtoInterface::class)) {
            throw new InvalidDtoReflection($this->dtoReflection->getName());
        }

        $this->dtoPublicProperties = array_map(
            function (ReflectionProperty $value): string {
                return $value->getName();
            },
            $this->dtoReflection->getProperties(ReflectionProperty::IS_PUBLIC),
        );
    }

    /**
     * @return array<string>
     */
    public function getDTOProperties(): array
    {
        return $this->dtoPublicProperties;
    }

    /**
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     */
    public function makePropertyMap(
        string $dtoPropertyName,
        ?string $entityGetterMethodName = '',
        string $arrayKeyName = '',
    ): PropertyMap {

        if (!in_array($dtoPropertyName, $this->dtoPublicProperties)) {
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
}
