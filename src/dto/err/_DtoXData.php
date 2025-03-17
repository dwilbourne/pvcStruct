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
            InvalidDtoClassException::class => 1000,
            DtoInvalidPropertyNameException::class => 1001,
            DtoInvalidArrayKeyException::class => 1002,
            DtoInvalidEntityGetterException::class => 1003,
            InvalidDtoReflection::class => 1004,
            DtoClassDefinitionException::class => 1005,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            DtoInvalidPropertyNameException::class => 'Property name (\'${propertyName}\') does not exist in the supplied DTO.',
            DtoInvalidArrayKeyException::class => 'Array key (\'${arrayKey}\') does not exist in the source array.',
            DtoInvalidEntityGetterException::class => 'Method ${getterName} does not exist in class ${entityClassString}',
            InvalidDtoReflection::class => '${badClassString} does not implement DtoInterface',
            InvalidDtoClassException::class => 'Invalid Dto class ${badDtoClassName}. Dto constructor must populate all public properties.',
            DtoClassDefinitionException::class => 'Bad class definition for ${badDtoClassName}.  Some public properties are not initialized in the constructor',
        ];
    }
}
