<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\tree\dto;

use pvc\interfaces\struct\tree\dto\TreenodeDtoFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\struct\dto\DtoFactoryAbstract;
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
    /**
     * makeDTO
     * @param array<TreenodeDtoShape>|object $source
     * @return TreenodeDto<PayloadType>
     */
    public function makeDto(array|object $source): TreenodeDto
    {
        /** @var TreenodeDto<PayloadType> $dto */
        $dto = new TreenodeDto();
        $this->hydrate($dto, $source);
        return $dto;
    }
}
