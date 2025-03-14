<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\dto;

/**
 * Class PropertyMap
 */
readonly class PropertyMap
{
    public function __construct(
        public string $dtoPropertyName,
        public string $entityGetterMethodName,
        /**
         * @var array-key&string
         */
        public string $arrayKeyName,
    ) {
    }
}
