<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto;

use pvc\interfaces\struct\tree\dto\TreenodeDtoFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\struct\dto\DtoFactoryAbstract;
use pvc\struct\dto\PropertyMapFactory;
use ReflectionClass;

/**
 * @template PayloadType
 * @template T of ReflectionClass
 * @extends DtoFactoryAbstract<T>
 * @implements TreenodeDtoFactoryInterface<PayloadType>
 * @phpstan-import-type TreenodeDtoShape from TreenodeDtoInterface
 */
class TreenodeDtoFactory extends DtoFactoryAbstract implements TreenodeDtoFactoryInterface
{
    public function __construct(PropertyMapFactory $propertyMapFactory, protected bool $ordered)
    {
        parent::__construct($propertyMapFactory);
    }

    /**
     * makeDTO
     * @param array<TreenodeDtoShape>|object $source
     * @return TreenodeDtoUnordered<PayloadType>
     */
    public function makeDto(array|object $source): TreenodeDtoUnordered
    {
        /** @var TreenodeDtoUnordered<PayloadType> $dto */
        $dto = $this->ordered? new TreenodeDtoOrdered() : new TreenodeDtoUnordered();
        $this->hydrate($dto, $source);
        return $dto;
    }
}
