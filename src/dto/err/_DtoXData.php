<?php
/** @noinspection SpellCheckingInspection */

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare(strict_types=1);

namespace pvc\struct\dto\err;

use pvc\err\XDataAbstract;

class _DtoXData extends XDataAbstract
{

    public function getLocalXCodes(): array
    {
        return [
            DtoInvalidPropertyValueException::class => 1000,
            DtoInvalidPropertyNameException::class => 1001,
            DtoInvalidArrayKeyException::class => 1002,
            DtoInvalidEntityGetterException::class => 1003,
            InvalidDtoReflection::class => 1004,
            PropertMapInvalidKeyException::class => 1005,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            DtoInvalidPropertyValueException::class => 'DTO ${className} error - cannot assign value ${value} to property ${propertyName}',
            DtoInvalidPropertyNameException::class => 'Property name (\'${propertyName}\') does not exist in the supplied DTO.',
            DtoInvalidArrayKeyException::class => 'Array key (\'${arrayKey}\') does not exist in the source array.',
            DtoInvalidEntityGetterException::class => 'Method ${getterName} does not exist in class ${entityClassString}',
            InvalidDtoReflection::class => '${badClassString} does not implement DtoInterface',
            PropertMapInvalidKeyException::class => '${key} does not exist in property map.',
        ];
    }
}
