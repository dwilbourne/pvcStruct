<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare(strict_types=1);

namespace pvc\struct\collection\err;


use pvc\err\XDataAbstract;

class _CollectionXData extends XDataAbstract
{
    /**
     * getLocalXCodes
     * @return int[]
     */
    public function getLocalXCodes(): array
    {
        return [
            DuplicateKeyException::class => 1001,
            InvalidKeyException::class => 1002,
            NonExistentKeyException::class => 1003,
        ];
    }

    /**
     * getXMessageTemplates
     * @return string[]
     */
    public function getXMessageTemplates(): array
    {
        return [
            DuplicateKeyException::class => 'duplicate list key ${duplicateKey}',
            InvalidKeyException::class => 'Invalid list key ${invalidKey}',
            NonExistentKeyException::class => 'non-existent list key ${nonExistentKey}',
        ];
    }
}
