<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare(strict_types=1);

namespace pvc\struct\collection\err;


use pvc\err\XDataAbstract;
use pvc\struct\collection\UnsetIndexException;

class _CollectionXData extends XDataAbstract
{
    /**
     * getLocalXCodes
     *
     * @return int[]
     */
    public function getLocalXCodes(): array
    {
        return [
            DuplicateKeyException::class   => 1001,
            InvalidKeyException::class     => 1002,
            NonExistentKeyException::class => 1003,
            ComparatorException::class     => 1004,
            InvalidValueException::class   => 1005,
        ];
    }

    /**
     * getXMessageTemplates
     *
     * @return string[]
     */
    public function getXMessageTemplates(): array
    {
        return [
            DuplicateKeyException::class   => 'duplicate list key ${duplicateKey}',
            InvalidKeyException::class     => 'Invalid key ${invalidKey}',
            NonExistentKeyException::class => 'non-existent key ${nonExistentKey}',
            InvalidValueException::class   => 'cannot put invalid value into the collection',
            ComparatorException::class     => 'cannot set a comparator on an indexed collection',
        ];
    }
}
